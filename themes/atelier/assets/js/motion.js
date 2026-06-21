/**
 * Atelier — motion system.
 *
 * A quiet, gallery-grade motion layer:
 *   - gentle fade/rise reveals on scroll (with a staggered project grid),
 *   - the SIGNATURE clip-path image reveal: each project cover wipes in from a
 *     thin slit to its full frame as it scrolls into view,
 *   - the SIGNATURE cursor-following caption: a refined floating label that
 *     trails the pointer while it is over a project cover, naming the piece,
 *   - a word-by-word rise on the studio-statement headline.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately and wire up nothing animated (no clip-path, no caption).
 *   - Performance. We animate only transform, opacity and clip-path, observe
 *     with a sensible rootMargin, unobserve after revealing, and drive the
 *     caption from a single rAF loop.
 *   - Accessibility. Decorative bits are aria-hidden; focus is never moved,
 *     trapped, or hidden by motion; the caption is pointer-only.
 */
( function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var canObserve = 'IntersectionObserver' in window;

	/**
	 * Reveal a fade/rise block immediately and for good.
	 *
	 * @param {Element} el The element to reveal.
	 */
	function revealNow( el ) {
		el.classList.add( 'is-inview' );

		if ( el.hasAttribute( 'data-atelier-words' ) ) {
			el.classList.add( 'words-in' );
		}
	}

	/**
	 * Run the signature clip-path reveal on a media frame.
	 *
	 * @param {Element} el The .entry-media[data-atelier-clip] element.
	 */
	function revealClip( el ) {
		el.classList.add( 'is-revealed' );
	}

	/**
	 * Split a headline's text into per-word spans so each word can rise in.
	 * Whitespace is preserved so the line wraps and reads identically to a
	 * screen reader (the original text stays intact as the concatenation of the
	 * word spans).
	 *
	 * @param {Element} host The [data-atelier-words] element.
	 * @return {boolean} Whether the split produced any word spans.
	 */
	function splitWords( host ) {
		var target = host.querySelector( '.studio-intro__text, .entry-title__text' );

		if ( ! target ) {
			if ( host.children.length ) {
				return false;
			}
			target = host;
		}

		var text = target.textContent;
		if ( ! text || ! text.trim() ) {
			return false;
		}

		var tokens = text.split( /(\s+)/ );
		var frag = doc.createDocumentFragment();
		var delay = 0;
		var made = false;

		for ( var i = 0; i < tokens.length; i++ ) {
			var token = tokens[ i ];
			if ( '' === token ) {
				continue;
			}

			if ( /^\s+$/.test( token ) ) {
				frag.appendChild( doc.createTextNode( token ) );
				continue;
			}

			var word = doc.createElement( 'span' );
			word.className = 'at-word';
			word.textContent = token;
			word.style.setProperty( '--w-delay', delay.toFixed( 2 ) + 's' );
			delay = Math.min( delay + 0.06, 0.72 );
			frag.appendChild( word );
			made = true;
		}

		if ( ! made ) {
			return false;
		}

		target.textContent = '';
		target.appendChild( frag );
		return true;
	}

	/**
	 * The signature cursor-following caption.
	 *
	 * A single fixed-position label is created once and trails the pointer while
	 * it hovers any project cover that carries data-atelier-caption. It is built
	 * and positioned entirely here, hidden from assistive tech, pointer-only,
	 * and skipped under reduced motion.
	 */
	function initCursorCaption() {
		var covers = doc.querySelectorAll( '[data-atelier-caption]' );
		if ( ! covers.length ) {
			return;
		}

		// Pointer-only: bail on touch/coarse pointers entirely (no element made).
		if ( window.matchMedia && window.matchMedia( '(hover: none), (pointer: coarse)' ).matches ) {
			return;
		}

		var caption = doc.createElement( 'div' );
		caption.className = 'atelier-caption';
		caption.setAttribute( 'aria-hidden', 'true' );

		var mark = doc.createElement( 'span' );
		mark.className = 'atelier-caption__mark';
		mark.textContent = '◇';

		var label = doc.createElement( 'span' );
		label.className = 'atelier-caption__text';

		caption.appendChild( mark );
		caption.appendChild( label );
		doc.body.appendChild( caption );

		var targetX = 0;
		var targetY = 0;
		var curX = 0;
		var curY = 0;
		var visible = false;
		var rafId = null;

		function loop() {
			// Ease the caption toward the pointer so it trails with a little drift.
			curX += ( targetX - curX ) * 0.2;
			curY += ( targetY - curY ) * 0.2;
			caption.style.transform =
				'translate(' + curX.toFixed( 1 ) + 'px, ' + curY.toFixed( 1 ) + 'px) translate(-50%, -140%)' +
				( visible ? ' scale(1)' : ' scale(0.92)' );

			if ( visible || Math.abs( targetX - curX ) > 0.5 || Math.abs( targetY - curY ) > 0.5 ) {
				rafId = window.requestAnimationFrame( loop );
			} else {
				rafId = null;
			}
		}

		function ensureLoop() {
			if ( null === rafId ) {
				rafId = window.requestAnimationFrame( loop );
			}
		}

		function show( text ) {
			label.textContent = text;
			visible = true;
			caption.classList.add( 'is-visible' );
			ensureLoop();
		}

		function hide() {
			visible = false;
			caption.classList.remove( 'is-visible' );
			ensureLoop();
		}

		for ( var i = 0; i < covers.length; i++ ) {
			( function ( cover ) {
				var text = cover.getAttribute( 'data-atelier-caption' ) || '';

				cover.addEventListener( 'pointerenter', function ( event ) {
					if ( 'touch' === event.pointerType ) {
						return;
					}
					targetX = curX = event.clientX;
					targetY = curY = event.clientY;
					show( text );
				} );

				cover.addEventListener( 'pointermove', function ( event ) {
					targetX = event.clientX;
					targetY = event.clientY;
					ensureLoop();
				} );

				cover.addEventListener( 'pointerleave', function () {
					hide();
				} );
			}( covers[ i ] ) );
		}

		// Safety: hide if the pointer leaves the document or the tab is hidden.
		doc.addEventListener( 'visibilitychange', function () {
			if ( doc.hidden ) {
				hide();
			}
		} );
	}

	/**
	 * Wire up scroll reveals and the signature clip-path covers.
	 */
	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-atelier-reveal]' );
		var words = doc.querySelectorAll( '[data-atelier-words]' );
		var clips = doc.querySelectorAll( '.entry-media[data-atelier-clip]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything. Split headlines (purely
			// cosmetic) and leave every block in its visible end-state.
			for ( i = 0; i < words.length; i++ ) {
				splitWords( words[ i ] );
			}
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			for ( i = 0; i < words.length; i++ ) {
				revealNow( words[ i ] );
			}
			for ( i = 0; i < clips.length; i++ ) {
				revealClip( clips[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0, the
		// clipped slit) only apply under `.atelier-motion`, so adding it before we
		// split/observe keeps the hidden window as small as possible — and if this
		// script had never run, the class would be absent and all content visible.
		root.classList.add( 'atelier-motion' );

		for ( i = 0; i < words.length; i++ ) {
			if ( splitWords( words[ i ] ) ) {
				words[ i ].classList.add( 'words-ready' );
			}
		}

		var revealObserver = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					if ( entries[ j ].isIntersecting ) {
						revealNow( entries[ j ].target );
						obs.unobserve( entries[ j ].target );
					}
				}
			},
			{
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.08,
			}
		);

		// The signature clip-path reveal gets its own observer with a slightly
		// later trigger so covers wipe in once they are comfortably on screen.
		var clipObserver = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					if ( entries[ j ].isIntersecting ) {
						revealClip( entries[ j ].target );
						obs.unobserve( entries[ j ].target );
					}
				}
			},
			{
				rootMargin: '0px 0px -12% 0px',
				threshold: 0.18,
			}
		);

		// Accessibility: if focus moves into a still-hidden reveal block, reveal it
		// permanently and stop observing so the focus ring is never on an invisible
		// element. (CSS :focus-within is the immediate safety net.)
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-atelier-reveal], [data-atelier-words]' );
				if ( block && ! block.classList.contains( 'is-inview' ) ) {
					revealNow( block );
					revealObserver.unobserve( block );
				}
			},
			true
		);

		for ( i = 0; i < nodes.length; i++ ) {
			revealObserver.observe( nodes[ i ] );
		}

		for ( i = 0; i < words.length; i++ ) {
			if ( ! words[ i ].hasAttribute( 'data-atelier-reveal' ) ) {
				revealObserver.observe( words[ i ] );
			}
		}

		for ( i = 0; i < clips.length; i++ ) {
			clipObserver.observe( clips[ i ] );
		}
	}

	function start() {
		// Reduced motion: reveal everything, wire nothing animated. The caption
		// and clip reveal are decorative, so they are simply skipped.
		if ( reduceMotion ) {
			var all = doc.querySelectorAll(
				'[data-atelier-reveal], [data-atelier-words]'
			);
			for ( var i = 0; i < all.length; i++ ) {
				revealNow( all[ i ] );
			}
			var clips = doc.querySelectorAll( '.entry-media[data-atelier-clip]' );
			for ( var k = 0; k < clips.length; k++ ) {
				revealClip( clips[ k ] );
			}
			return;
		}

		initReveals();
		initCursorCaption();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
