/**
 * Hearth — motion system.
 *
 * A warm, hospitable motion layer: the SIGNATURE gentle fade-up reveal on
 * scroll (with a tasteful stagger across the menu grid), the "open now" hours
 * pip that warms up with a slow pulse, and a slim reading-progress bar on
 * single posts.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately and arm no animation (the CSS also force-resets start states).
 *   - Performance. We animate only transform and opacity, observe with a
 *     sensible rootMargin, and unobserve each element after it reveals.
 *   - Accessibility. Decorative bits are aria-hidden; focus is never moved,
 *     trapped, or hidden by motion.
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
	 * Reveal an element immediately and for good (no animation state left
	 * behind). Used both as the IO callback and as the reduced-motion /
	 * no-support fallback.
	 *
	 * @param {Element} el The element to reveal.
	 */
	function revealNow( el ) {
		el.classList.add( 'is-inview' );
	}

	/**
	 * Reading-progress bar: maps scroll position through the article body to a
	 * 0..1 scaleX on the bar. rAF-throttled, transform-only, no layout reads
	 * during the animation frame beyond a cached rect.
	 */
	function initReadingProgress() {
		var wrap = doc.querySelector( '.reading-progress' );
		if ( ! wrap ) {
			return;
		}

		var bar = wrap.querySelector( '.reading-progress__bar' );
		var article = doc.querySelector( '.entry--singular' );
		if ( ! bar || ! article ) {
			return;
		}

		wrap.hidden = false;
		var ticking = false;

		function update() {
			ticking = false;

			var rect = article.getBoundingClientRect();
			var viewport = window.innerHeight || root.clientHeight;
			var total = rect.height - viewport;
			var progress;

			if ( total <= 0 ) {
				progress = 1;
			} else {
				progress = ( -rect.top ) / total;
			}

			if ( progress < 0 ) {
				progress = 0;
			} else if ( progress > 1 ) {
				progress = 1;
			}

			bar.style.transform = 'scaleX(' + progress + ')';
		}

		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	/**
	 * Wire up all scroll reveals — the signature warm fade-up.
	 */
	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-hearth-reveal]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything.
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.hearth-motion`, so adding it before we observe keeps the
		// hidden window as small as possible — and if this script had never run,
		// the class would be absent and all content would be visible.
		root.classList.add( 'hearth-motion' );

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];
					if ( entry.isIntersecting ) {
						revealNow( entry.target );
						obs.unobserve( entry.target );
					}
				}
			},
			{
				// Reveal a touch before the element reaches the fold, and only
				// once a sliver is on screen — feels responsive, never abrupt.
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.08,
			}
		);

		// Accessibility: if focus moves into a still-hidden reveal block (e.g. a
		// keyboard user tabs to an off-screen link before it has scrolled in),
		// reveal it permanently and stop observing so the focus ring is never
		// sitting on an invisible element.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-hearth-reveal]' );
				if ( block && ! block.classList.contains( 'is-inview' ) ) {
					revealNow( block );
					observer.unobserve( block );
				}
			},
			true
		);

		for ( i = 0; i < nodes.length; i++ ) {
			observer.observe( nodes[ i ] );
		}
	}

	function start() {
		// Reduced motion: reveal everything, arm nothing animated. Reading
		// progress is informational, so we still show it but it jumps rather
		// than eases (CSS disables its transition).
		if ( reduceMotion ) {
			var all = doc.querySelectorAll( '[data-hearth-reveal]' );
			for ( var i = 0; i < all.length; i++ ) {
				revealNow( all[ i ] );
			}
			initReadingProgress();
			return;
		}

		initReveals();
		initReadingProgress();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
