/**
 * Contents — Smart Table of Contents (behavior layer).
 *
 * Progressive enhancement for the auto-generated TOC. Everything here is optional
 * polish: with this file absent, blocked, or erroring, the TOC is still a working
 * list of in-page anchor links and the collapse toggle is open.
 *
 * What it adds:
 *   - Smooth scroll to a section when a link is activated, honoring
 *     prefers-reduced-motion (instant jump when reduced). After scrolling, focus
 *     moves to the target heading so keyboard / screen-reader users land there,
 *     and the URL hash is updated without a second jump.
 *   - Active-section highlighting via IntersectionObserver: the link for the
 *     section currently in view gets aria-current="true" and an .is-active class.
 *   - Collapse / expand of the list (driven by the native <button> toggle), which
 *     CSS only surfaces on small screens. The toggle reflects state through
 *     aria-expanded so it is correct for assistive technology.
 *
 * Constraints, kept in lockstep with the CSS:
 *   - Animate transform / opacity only; the collapse animates grid-template-rows
 *     which is GPU-cheap and causes no surrounding layout shift.
 *   - prefers-reduced-motion is read live (no reload needed) and also enforced by
 *     the stylesheet. No globals, no dependencies, no network, no console noise.
 *
 * @package ContentsToc
 */
( function () {
	'use strict';

	var nav = document.querySelector( '[data-contents-toc]' );
	if ( ! nav ) {
		return;
	}

	var links = Array.prototype.slice.call(
		nav.querySelectorAll( '[data-contents-target]' )
	);
	if ( ! links.length ) {
		return;
	}

	// Live reduced-motion query so toggling the OS setting needs no reload;
	// degrade gracefully where matchMedia is unavailable.
	var reducedMotionQuery =
		typeof window.matchMedia === 'function'
			? window.matchMedia( '(prefers-reduced-motion: reduce)' )
			: { matches: false, addEventListener: null, addListener: null };

	function prefersReducedMotion() {
		return !! reducedMotionQuery.matches;
	}

	// Mark the document JS-ready so CSS can hand the collapse animation over to
	// the script. Until this lands the list is always expanded (no-JS safe).
	nav.classList.add( 'contents-toc--js' );

	/* ---------------------------------------------------------------------
	 * Map each link to its target heading once, up front.
	 * ------------------------------------------------------------------- */
	var targets = []; // { link, heading, id }
	links.forEach( function ( link ) {
		var id = link.getAttribute( 'data-contents-target' );
		if ( ! id ) {
			return;
		}
		var heading = document.getElementById( id );
		if ( heading ) {
			targets.push( { link: link, heading: heading, id: id } );
		}
	} );

	/* ---------------------------------------------------------------------
	 * Smooth scroll + focus management on link activation.
	 * ------------------------------------------------------------------- */
	function focusHeading( heading ) {
		if ( ! heading || typeof heading.focus !== 'function' ) {
			return;
		}

		var hadTabindex = heading.hasAttribute( 'tabindex' );
		if ( ! hadTabindex ) {
			// Make the heading programmatically focusable without putting it in
			// the tab order; remove it again on blur so the DOM stays clean.
			heading.setAttribute( 'tabindex', '-1' );
		}

		heading.focus( { preventScroll: true } );

		if ( ! hadTabindex ) {
			heading.addEventListener( 'blur', function handler() {
				heading.removeAttribute( 'tabindex' );
				heading.removeEventListener( 'blur', handler );
			} );
		}
	}

	function scrollToHeading( heading, id ) {
		var behavior = prefersReducedMotion() ? 'auto' : 'smooth';

		try {
			heading.scrollIntoView( { behavior: behavior, block: 'start' } );
		} catch ( err ) {
			// Older browsers without the options-object form.
			heading.scrollIntoView();
		}

		focusHeading( heading );

		// Reflect the section in the URL without triggering a second jump.
		if ( id && window.history && typeof window.history.replaceState === 'function' ) {
			try {
				window.history.replaceState( null, '', '#' + id );
			} catch ( err ) {
				// Ignore (e.g. sandboxed history); the scroll already happened.
			}
		}
	}

	targets.forEach( function ( t ) {
		t.link.addEventListener( 'click', function ( event ) {
			// Let modified clicks (open in new tab, etc.) behave normally.
			if (
				event.defaultPrevented ||
				event.button !== 0 ||
				event.metaKey ||
				event.ctrlKey ||
				event.shiftKey ||
				event.altKey
			) {
				return;
			}

			event.preventDefault();
			scrollToHeading( t.heading, t.id );
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Active-section highlighting via IntersectionObserver.
	 *
	 * We track which headings are within an upper band of the viewport and mark
	 * the lowest-positioned visible one as active (the section the reader has
	 * most recently scrolled into). One link at a time carries the state.
	 * ------------------------------------------------------------------- */
	function setActive( id ) {
		targets.forEach( function ( t ) {
			var isActive = t.id === id;
			t.link.classList.toggle( 'is-active', isActive );
			if ( isActive ) {
				t.link.setAttribute( 'aria-current', 'true' );
			} else {
				t.link.removeAttribute( 'aria-current' );
			}
		} );
	}

	if ( typeof window.IntersectionObserver === 'function' && targets.length ) {
		var visible = {}; // id -> true for headings currently in the band.

		function pickActive() {
			// Choose the visible heading nearest the top of the document order
			// that is in view; if none are in view, keep the last active link.
			var chosen = null;
			for ( var i = 0; i < targets.length; i++ ) {
				if ( visible[ targets[ i ].id ] ) {
					chosen = targets[ i ].id;
				}
			}
			if ( chosen ) {
				setActive( chosen );
			}
		}

		var observer = new window.IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					var id = entry.target.id;
					if ( entry.isIntersecting ) {
						visible[ id ] = true;
					} else {
						delete visible[ id ];
					}
				} );
				pickActive();
			},
			{
				// A band across the upper portion of the viewport: a heading is
				// "current" while it sits in the top ~35% reading zone.
				root: null,
				rootMargin: '0px 0px -65% 0px',
				threshold: 0,
			}
		);

		targets.forEach( function ( t ) {
			observer.observe( t.heading );
		} );

		// Seed an initial active link from the current hash, if it matches.
		if ( window.location.hash ) {
			var hashId = window.location.hash.slice( 1 );
			for ( var k = 0; k < targets.length; k++ ) {
				if ( targets[ k ].id === hashId ) {
					setActive( hashId );
					break;
				}
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Collapse / expand (surfaced by CSS on small screens).
	 * ------------------------------------------------------------------- */
	var toggle = nav.querySelector( '.contents-toc__toggle' );
	var list = nav.querySelector( '.contents-toc__list' );

	if ( toggle && list ) {
		// Default to collapsed on small screens so the TOC does not push the
		// article down; expanded otherwise. matchMedia keeps this in sync.
		var smallScreenQuery =
			typeof window.matchMedia === 'function'
				? window.matchMedia( '(max-width: 640px)' )
				: { matches: false, addEventListener: null, addListener: null };

		function setExpanded( expanded ) {
			toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			nav.classList.toggle( 'is-collapsed', ! expanded );
		}

		// Initial state follows viewport size.
		setExpanded( ! smallScreenQuery.matches );

		toggle.addEventListener( 'click', function () {
			var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			setExpanded( ! expanded );
		} );

		// Re-evaluate the default when crossing the breakpoint, but only if the
		// user has not manually overridden it this session.
		var userToggled = false;
		toggle.addEventListener( 'click', function () {
			userToggled = true;
		} );

		function onBreakpoint() {
			if ( ! userToggled ) {
				setExpanded( ! smallScreenQuery.matches );
			}
		}

		if ( typeof smallScreenQuery.addEventListener === 'function' ) {
			smallScreenQuery.addEventListener( 'change', onBreakpoint );
		} else if ( typeof smallScreenQuery.addListener === 'function' ) {
			smallScreenQuery.addListener( onBreakpoint ); // Safari < 14.
		}
	}
}() );
