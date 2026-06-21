/**
 * Lumen — Image Lightbox (behavior layer).
 *
 * Progressive enhancement for content/gallery images tagged server-side with
 * `data-lumen`. With this file absent, blocked, or erroring, the images are
 * ordinary images and the page is unchanged.
 *
 * What it adds:
 *   - Click (or Enter/Space when focused) on a tagged image opens one shared
 *     full-screen overlay (role="dialog", aria-modal) showing the full-size
 *     image and its caption (figcaption, falling back to alt).
 *   - Keyboard: ArrowLeft/ArrowRight move between images, Escape closes, Home/End
 *     jump to the first/last image. A focus trap keeps Tab inside the dialog.
 *   - Prev/Next buttons appear only when more than one image exists; a counter
 *     and a polite live region announce position changes to assistive tech.
 *   - Focus is moved into the dialog on open and RESTORED to the triggering image
 *     on close.
 *   - Smooth fade + zoom that animates transform/opacity only. prefers-reduced-
 *     motion is read live (JS guard) and also enforced by the stylesheet, so
 *     reduced-motion visitors get instant open/close with no animation.
 *
 * Constraints (kept in lockstep with the CSS): no globals, no dependencies, no
 * network, no console noise; animate transform/opacity only; zero layout shift.
 *
 * @package Lumen
 */
( function () {
	'use strict';

	var triggers = Array.prototype.slice.call(
		document.querySelectorAll( '[data-lumen]' )
	);
	if ( ! triggers.length ) {
		return;
	}

	var overlay = document.querySelector( '[data-lumen-overlay]' );
	if ( ! overlay ) {
		return;
	}

	// Cached overlay parts.
	var imageEl = overlay.querySelector( '[data-lumen-image]' );
	var captionEl = overlay.querySelector( '[data-lumen-caption]' );
	var counterEl = overlay.querySelector( '[data-lumen-counter]' );
	var liveEl = overlay.querySelector( '[data-lumen-live]' );
	var prevBtn = overlay.querySelector( '[data-lumen-prev]' );
	var nextBtn = overlay.querySelector( '[data-lumen-next]' );
	var closeBtn = overlay.querySelector( '[data-lumen-close]' );
	var closeRegions = Array.prototype.slice.call(
		overlay.querySelectorAll( '[data-lumen-close]' )
	);

	if ( ! imageEl ) {
		return;
	}

	// Localized strings (with safe fallbacks if the handle is missing).
	var l10n =
		typeof window.lumenL10n === 'object' && window.lumenL10n
			? window.lumenL10n
			: {};
	var counterTpl = l10n.counter || 'Image %1$s of %2$s';

	// Live reduced-motion query so toggling the OS setting needs no reload;
	// degrade gracefully where matchMedia is unavailable.
	var reducedMotionQuery =
		typeof window.matchMedia === 'function'
			? window.matchMedia( '(prefers-reduced-motion: reduce)' )
			: { matches: false };

	function prefersReducedMotion() {
		return !! reducedMotionQuery.matches;
	}

	/* ---------------------------------------------------------------------
	 * Build the image list from the tagged triggers.
	 * Each entry: { trigger, full, caption, alt }.
	 * ------------------------------------------------------------------- */
	var items = triggers
		.map( function ( img ) {
			var full =
				img.getAttribute( 'data-lumen-full' ) ||
				img.getAttribute( 'src' ) ||
				'';
			if ( ! full ) {
				return null;
			}
			return {
				trigger: img,
				full: full,
				caption: img.getAttribute( 'data-lumen-caption' ) || '',
				alt: img.getAttribute( 'alt' ) || '',
			};
		} )
		.filter( Boolean );

	if ( ! items.length ) {
		return;
	}

	var multiple = items.length > 1;
	var currentIndex = -1;
	var lastFocused = null; // The element to restore focus to on close.
	var isOpen = false;

	/* ---------------------------------------------------------------------
	 * Make each trigger image interactive (cursor, role, keyboard).
	 * ------------------------------------------------------------------- */
	items.forEach( function ( item, index ) {
		var img = item.trigger;
		img.classList.add( 'lumen-trigger' );
		img.setAttribute( 'tabindex', '0' );
		img.setAttribute( 'role', 'button' );

		img.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			open( index, img );
		} );

		img.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar' ) {
				event.preventDefault();
				open( index, img );
			}
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Focus trap helpers.
	 * ------------------------------------------------------------------- */
	function focusableInOverlay() {
		var nodes = overlay.querySelectorAll(
			'button:not([hidden]):not([disabled]), [href], [tabindex]:not([tabindex="-1"])'
		);
		return Array.prototype.filter.call( nodes, function ( el ) {
			// Exclude elements hidden via CSS or an ancestor.
			return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
		} );
	}

	function trapTab( event ) {
		var focusable = focusableInOverlay();
		if ( ! focusable.length ) {
			event.preventDefault();
			return;
		}

		var first = focusable[ 0 ];
		var last = focusable[ focusable.length - 1 ];
		var active = document.activeElement;

		if ( event.shiftKey ) {
			if ( active === first || ! overlay.contains( active ) ) {
				event.preventDefault();
				last.focus();
			}
		} else if ( active === last || ! overlay.contains( active ) ) {
			event.preventDefault();
			first.focus();
		}
	}

	/* ---------------------------------------------------------------------
	 * Render the current item into the overlay.
	 * ------------------------------------------------------------------- */
	function render() {
		var item = items[ currentIndex ];
		if ( ! item ) {
			return;
		}

		// Swap the source; clear first so a slow load doesn't show the prior image.
		imageEl.setAttribute( 'alt', item.alt );
		imageEl.classList.remove( 'is-loaded' );
		imageEl.src = item.full;

		// onload toggles a class the CSS uses to fade the new image in.
		if ( imageEl.complete && imageEl.naturalWidth ) {
			imageEl.classList.add( 'is-loaded' );
		}

		// Caption.
		if ( captionEl ) {
			if ( item.caption ) {
				captionEl.textContent = item.caption;
				captionEl.hidden = false;
			} else {
				captionEl.textContent = '';
				captionEl.hidden = true;
			}
		}

		// Counter + live announcement (only meaningful with multiple images).
		if ( multiple ) {
			var label = counterTpl
				.replace( '%1$s', String( currentIndex + 1 ) )
				.replace( '%2$s', String( items.length ) );
			if ( counterEl ) {
				counterEl.textContent = label;
				counterEl.hidden = false;
			}
			if ( liveEl ) {
				liveEl.textContent = label;
			}
		}
	}

	imageEl.addEventListener( 'load', function () {
		imageEl.classList.add( 'is-loaded' );
	} );

	/* ---------------------------------------------------------------------
	 * Open / navigate / close.
	 * ------------------------------------------------------------------- */
	function open( index, fromEl ) {
		if ( index < 0 || index >= items.length ) {
			return;
		}

		lastFocused = fromEl || document.activeElement;
		currentIndex = index;
		isOpen = true;

		// Reveal prev/next only when there is more than one image.
		if ( multiple ) {
			if ( prevBtn ) {
				prevBtn.hidden = false;
			}
			if ( nextBtn ) {
				nextBtn.hidden = false;
			}
		}

		render();

		overlay.hidden = false;
		// Force a reflow so the entrance transition runs from the primed state.
		// (Reading offsetWidth flushes pending style/layout.)
		void overlay.offsetWidth;
		overlay.classList.add( 'is-open' );

		// Lock background scroll without shifting layout.
		document.documentElement.classList.add( 'lumen-locked' );

		document.addEventListener( 'keydown', onKeydown, true );

		// Move focus into the dialog. Prefer the close button as a stable anchor.
		var target = closeBtn || overlay;
		// focus() after the frame so the dialog is laid out and focusable.
		window.requestAnimationFrame( function () {
			if ( target && typeof target.focus === 'function' ) {
				target.focus();
			}
		} );
	}

	function go( delta ) {
		if ( ! isOpen ) {
			return;
		}
		var next = currentIndex + delta;
		// Wrap around for a continuous gallery.
		if ( next < 0 ) {
			next = items.length - 1;
		} else if ( next >= items.length ) {
			next = 0;
		}
		currentIndex = next;
		render();
	}

	function goTo( index ) {
		if ( ! isOpen || index < 0 || index >= items.length ) {
			return;
		}
		currentIndex = index;
		render();
	}

	function close() {
		if ( ! isOpen ) {
			return;
		}
		isOpen = false;

		overlay.classList.remove( 'is-open' );
		document.removeEventListener( 'keydown', onKeydown, true );
		document.documentElement.classList.remove( 'lumen-locked' );

		var finish = function () {
			overlay.hidden = true;
			imageEl.removeAttribute( 'src' );
			imageEl.classList.remove( 'is-loaded' );
			if ( liveEl ) {
				liveEl.textContent = '';
			}
			// Restore focus to the image that opened the lightbox.
			if ( lastFocused && typeof lastFocused.focus === 'function' ) {
				lastFocused.focus();
			}
			lastFocused = null;
		};

		// Wait for the exit transition unless motion is reduced.
		if ( prefersReducedMotion() ) {
			finish();
			return;
		}

		var done = false;
		var onEnd = function ( event ) {
			if ( event && event.target !== overlay ) {
				return; // Ignore bubbled child transitions.
			}
			if ( done ) {
				return;
			}
			done = true;
			overlay.removeEventListener( 'transitionend', onEnd );
			finish();
		};
		overlay.addEventListener( 'transitionend', onEnd );
		// Safety net in case transitionend never fires.
		window.setTimeout( function () {
			onEnd();
		}, 400 );
	}

	/* ---------------------------------------------------------------------
	 * Global key handling while open.
	 * ------------------------------------------------------------------- */
	function onKeydown( event ) {
		if ( ! isOpen ) {
			return;
		}

		switch ( event.key ) {
			case 'Escape':
			case 'Esc':
				event.preventDefault();
				close();
				break;
			case 'ArrowRight':
				if ( multiple ) {
					event.preventDefault();
					go( 1 );
				}
				break;
			case 'ArrowLeft':
				if ( multiple ) {
					event.preventDefault();
					go( -1 );
				}
				break;
			case 'Home':
				if ( multiple ) {
					event.preventDefault();
					goTo( 0 );
				}
				break;
			case 'End':
				if ( multiple ) {
					event.preventDefault();
					goTo( items.length - 1 );
				}
				break;
			case 'Tab':
				trapTab( event );
				break;
			default:
				break;
		}
	}

	/* ---------------------------------------------------------------------
	 * Overlay control wiring.
	 * ------------------------------------------------------------------- */
	closeRegions.forEach( function ( el ) {
		el.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			close();
		} );
	} );

	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			go( -1 );
		} );
	}
	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			go( 1 );
		} );
	}

	// A click on the figure/image itself should not close the overlay (only the
	// backdrop and explicit close controls do). Stop those clicks from bubbling
	// to the backdrop if they ever share a parent.
	var viewport = overlay.querySelector( '.lumen-overlay__viewport' );
	if ( viewport ) {
		viewport.addEventListener( 'click', function ( event ) {
			event.stopPropagation();
		} );
	}
}() );
