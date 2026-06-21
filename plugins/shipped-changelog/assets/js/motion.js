/**
 * Shipped — Auto Changelog & Roadmap: motion enhancement.
 *
 * Progressive enhancement only. Timeline entries and roadmap cards are fully
 * visible without this file; here we add a subtle, accessible entrance (fade +
 * slide up, in a gentle stagger) as each element scrolls into view.
 *
 * Design constraints (kept in lockstep with the CSS):
 * - Animate transform + opacity ONLY (no layout properties) — no jank, no CLS.
 * - Respect prefers-reduced-motion: reduce — if set, reveal everything at once
 *   and never animate. The CSS @media block mirrors this as a safety net.
 * - Reveal on IntersectionObserver with a forgiving rootMargin; unobserve each
 *   element after it has shown so we never re-run or hold references.
 * - No globals, no dependencies, no console noise. Defensive throughout: any
 *   missing API simply falls back to "show immediately".
 *
 * @package Shipped
 */
( function () {
	'use strict';

	var SELECTOR = '[data-shipped-reveal]';
	var PRIME_CLASS = 'shipped-will-reveal';
	var REVEAL_CLASS = 'shipped-is-revealed';
	var STAGGER_MS = 70; // Per-element delay so a group cascades in.
	var STAGGER_MAX = 6; // Cap the cascade so long lists don't drag.

	/**
	 * Mark an element as revealed (idempotent).
	 *
	 * @param {Element} el The element.
	 */
	function reveal( el ) {
		el.classList.remove( PRIME_CLASS );
		el.classList.add( REVEAL_CLASS );
	}

	/**
	 * Reveal every element immediately, with no animation.
	 *
	 * Universal fallback: reduced-motion, missing IntersectionObserver, or any
	 * unexpected error.
	 *
	 * @param {NodeList|Array} els The elements.
	 */
	function revealAll( els ) {
		for ( var i = 0; i < els.length; i++ ) {
			reveal( els[ i ] );
		}
	}

	/**
	 * Wire up the scroll-reveal behavior once the DOM is ready.
	 */
	function init() {
		var els = document.querySelectorAll( SELECTOR );

		if ( ! els.length ) {
			return;
		}

		// Honor the user's motion preference. If reduced motion is requested — or
		// matchMedia is unavailable — skip all animation and leave elements as-is
		// (the CSS already keeps them visible by default).
		var prefersReduced =
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( prefersReduced ) {
			return;
		}

		// Without IntersectionObserver we can't cheaply detect scroll-in, so just
		// reveal immediately rather than risk stuck-hidden entries.
		if ( typeof window.IntersectionObserver !== 'function' ) {
			revealAll( els );
			return;
		}

		// Prime elements into their pre-reveal (hidden) state. We do this in JS —
		// not in CSS-by-default — so that if this script never runs, nothing is
		// ever hidden.
		var i;
		for ( i = 0; i < els.length; i++ ) {
			els[ i ].classList.add( PRIME_CLASS );
		}

		// Track how many have revealed so the cascade staggers rather than firing
		// all at once.
		var revealedCount = 0;

		var observer = new window.IntersectionObserver(
			function ( entries, obs ) {
				// Reveal in DOM order for a tidy cascade even if the observer
				// reports entries out of order.
				entries.sort( function ( a, b ) {
					return a.target.compareDocumentPosition( b.target ) &
						Node.DOCUMENT_POSITION_FOLLOWING
						? -1
						: 1;
				} );

				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];

					if ( entry.isIntersecting ) {
						var step = Math.min( revealedCount, STAGGER_MAX );
						entry.target.style.setProperty(
							'--shipped-delay',
							step * STAGGER_MS + 'ms'
						);
						reveal( entry.target );
						revealedCount++;
						// One-shot: never observe a revealed element again.
						obs.unobserve( entry.target );
					}
				}
			},
			{
				root: null,
				rootMargin: '0px 0px -8% 0px',
				threshold: 0.01,
			}
		);

		for ( i = 0; i < els.length; i++ ) {
			observer.observe( els[ i ] );
		}
	}

	// Defensive bootstrap: the script is deferred, so the DOM is parsed by the
	// time it runs, but we guard anyway. Any thrown error reveals everything so
	// nothing can be left invisible.
	try {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
	} catch ( e ) {
		revealAll( document.querySelectorAll( SELECTOR ) );
	}
} )();
