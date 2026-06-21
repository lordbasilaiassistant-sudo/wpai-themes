/**
 * Dispatch — motion system.
 *
 * A crisp, newsroom motion layer: staggered slide-up reveals as the news grid
 * and card river scroll in (the signature move), plus a live headline ticker
 * marquee that scrolls the latest stories across a strip under the masthead.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible and the ticker is a static link list.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately and never start the marquee.
 *   - Performance. We animate only transform and opacity, observe with a
 *     sensible rootMargin, and unobserve each element after it reveals.
 *   - Accessibility. Decorative bits are aria-hidden; focus is never moved,
 *     trapped, or hidden by motion; the ticker pauses on hover/focus.
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
	 * Reveal an element immediately and for good.
	 *
	 * @param {Element} el The element to reveal.
	 */
	function revealNow( el ) {
		el.classList.add( 'is-inview' );
	}

	/**
	 * Apply a capped, deterministic stagger to a group of reveal elements so
	 * they breathe in one after another rather than all at once. The delay is
	 * written to an inline --d-stagger custom property the CSS reads.
	 *
	 * @param {NodeList|Array} group Elements to stagger.
	 */
	function stagger( group ) {
		var step = 0.07;
		var max = 0.42;
		for ( var i = 0; i < group.length; i++ ) {
			var delay = Math.min( i * step, max );
			group[ i ].style.setProperty( '--d-stagger', delay.toFixed( 2 ) + 's' );
		}
	}

	/**
	 * The signature headline ticker. Duplicates already exist in the markup
	 * (a real copy + an aria-hidden clone), so translating the track by -50%
	 * loops seamlessly. We only switch the animation on and size its duration
	 * to the content width so the scroll speed feels consistent.
	 */
	function initTicker() {
		var track = doc.querySelector( '.ticker__track' );
		if ( ! track ) {
			return;
		}

		// Duration scales with the content so a long headline set and a short
		// one scroll at a similar pace. Half the track is one full content copy.
		var width = track.scrollWidth / 2;
		if ( width > 0 ) {
			var speed = 70; // pixels per second
			var dur = Math.max( 18, Math.round( width / speed ) );
			track.style.setProperty( '--tk-dur', dur + 's' );
		}

		track.classList.add( 'is-animating' );
	}

	/**
	 * Wire up all scroll reveals with the staggered news-grid signature.
	 */
	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-dispatch-reveal]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything.
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.dispatch-motion`, so adding it before we observe keeps
		// the hidden window as small as possible — and if this script had never
		// run, the class would be absent and all content would be visible.
		root.classList.add( 'dispatch-motion' );

		// Stagger each group of sibling reveals that share a parent so a row of
		// secondary stories or a grid of cards cascades in order.
		var groups = {};
		for ( i = 0; i < nodes.length; i++ ) {
			var parent = nodes[ i ].parentNode;
			if ( ! parent ) {
				continue;
			}
			var key = parent.className || 'root';
			if ( ! groups[ key ] ) {
				groups[ key ] = [];
			}
			// Only stagger members of an explicit grid/river, not lone blocks.
			if (
				parent.classList &&
				( parent.classList.contains( 'news-grid__secondary' ) ||
					parent.classList.contains( 'entry-river' ) )
			) {
				groups[ key ].push( nodes[ i ] );
			}
		}
		for ( var g in groups ) {
			if ( Object.prototype.hasOwnProperty.call( groups, g ) ) {
				stagger( groups[ g ] );
			}
		}

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
				rootMargin: '0px 0px -8% 0px',
				threshold: 0.08,
			}
		);

		// Accessibility: if focus moves into a still-hidden reveal block, reveal
		// it permanently and stop observing it so the focus ring is never sitting
		// on an invisible element.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-dispatch-reveal]' );
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
		// Reduced motion: reveal all, wire no animation. The ticker stays a
		// static, readable list of links (the marquee never starts).
		if ( reduceMotion ) {
			var all = doc.querySelectorAll( '[data-dispatch-reveal]' );
			for ( var i = 0; i < all.length; i++ ) {
				revealNow( all[ i ] );
			}
			// Still add the motion class so the ticker CSS hooks exist, but do
			// NOT add is-animating — the strip stays still.
			return;
		}

		initReveals();
		initTicker();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
