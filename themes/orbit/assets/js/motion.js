/**
 * Orbit — motion system.
 *
 * A confident, technical motion layer for a dark dev-tool theme:
 *   - gentle fade/rise reveals on scroll, with a staggered variant for grids;
 *   - the SIGNATURE moves: a drifting starfield seeded into the hero, the
 *     magnetic CTA whose glow tracks the cursor, and count-up metrics that
 *     animate from zero when they scroll into view;
 *   - a slim reading-progress bar on single posts.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible and the final metric values (already in
 *     the markup) remain correct.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately, skip the count-up, and wire up no drift/magnet behaviour.
 *   - Performance. We animate only transform and opacity, observe with a
 *     sensible rootMargin, and unobserve each element after it fires.
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
	 * Reveal an element immediately and for good.
	 *
	 * @param {Element} el The element to reveal.
	 */
	function revealNow( el ) {
		el.classList.add( 'is-inview' );
	}

	/* --------------------------------------------------------------------- *
	 * Signature 1: the hero starfield.
	 * Seed a field of small circles into the hero's SVG, each with a random
	 * position, radius, and twinkle delay. Pure SVG, no images, no network.
	 * -------------------------------------------------------------------- */
	function initStarfield() {
		var svg = doc.querySelector( '[data-orbit-starfield]' );
		if ( ! svg ) {
			return;
		}

		var NS = 'http://www.w3.org/2000/svg';
		// viewBox is 600x400 (declared in PHP); seed proportional to area but
		// capped so we never paint an absurd number of nodes.
		var count = 64;
		var frag = doc.createDocumentFragment();

		for ( var i = 0; i < count; i++ ) {
			var star = doc.createElementNS( NS, 'circle' );
			var x = ( Math.random() * 600 ).toFixed( 1 );
			var y = ( Math.random() * 400 ).toFixed( 1 );
			var r = ( Math.random() * 1.4 + 0.4 ).toFixed( 2 );

			star.setAttribute( 'cx', x );
			star.setAttribute( 'cy', y );
			star.setAttribute( 'r', r );
			star.setAttribute( 'fill', '#ffffff' );
			star.setAttribute( 'class', 'hero__star' );
			// Stagger the twinkle so the field shimmers rather than blinks.
			star.style.animationDelay = ( Math.random() * 4 ).toFixed( 2 ) + 's';
			star.style.opacity = ( Math.random() * 0.5 + 0.2 ).toFixed( 2 );

			frag.appendChild( star );
		}

		svg.appendChild( frag );
	}

	/* --------------------------------------------------------------------- *
	 * Signature 2: the magnetic CTA.
	 * On pointer move within a [data-orbit-magnetic] wrapper, nudge it toward
	 * the cursor (transform only) and point the radial glow at the pointer.
	 * Resets cleanly on leave. Pointer-only; keyboard focus uses CSS.
	 * -------------------------------------------------------------------- */
	function initMagnetic() {
		var wraps = doc.querySelectorAll( '[data-orbit-magnetic]' );
		if ( ! wraps.length ) {
			return;
		}

		var STRENGTH = 0.28; // how far the button leans toward the cursor.

		for ( var i = 0; i < wraps.length; i++ ) {
			( function ( wrap ) {
				wrap.addEventListener( 'pointermove', function ( event ) {
					if ( event.pointerType === 'touch' ) {
						return;
					}
					var rect = wrap.getBoundingClientRect();
					var relX = event.clientX - rect.left;
					var relY = event.clientY - rect.top;
					var moveX = ( relX - rect.width / 2 ) * STRENGTH;
					var moveY = ( relY - rect.height / 2 ) * STRENGTH;

					wrap.style.transform =
						'translate(' + moveX.toFixed( 1 ) + 'px,' + moveY.toFixed( 1 ) + 'px)';
					wrap.style.setProperty( '--mx', relX.toFixed( 0 ) + 'px' );
					wrap.style.setProperty( '--my', relY.toFixed( 0 ) + 'px' );
				} );

				wrap.addEventListener( 'pointerleave', function () {
					wrap.style.transform = '';
				} );
			}( wraps[ i ] ) );
		}
	}

	/* --------------------------------------------------------------------- *
	 * Signature 3: count-up metrics.
	 * Animate each [data-orbit-count] from 0 to its target when it scrolls in.
	 * Preserves the number's formatting (decimals, value precision). The final
	 * value already lives in the markup, so reduced-motion / no-JS show it
	 * correctly without any animation.
	 * -------------------------------------------------------------------- */
	function countUp( el ) {
		var raw = el.getAttribute( 'data-orbit-count' );
		var target = parseFloat( raw );
		if ( isNaN( target ) ) {
			return;
		}

		// Match the source precision (e.g. "99.99" -> 2 decimals, "40" -> 0).
		var dot = raw.indexOf( '.' );
		var decimals = dot === -1 ? 0 : raw.length - dot - 1;

		var duration = 1400;
		var startTime = null;

		function frame( now ) {
			if ( startTime === null ) {
				startTime = now;
			}
			var elapsed = now - startTime;
			var t = Math.min( elapsed / duration, 1 );
			// easeOutCubic for a snappy-then-settle feel.
			var eased = 1 - Math.pow( 1 - t, 3 );
			var current = ( target * eased ).toFixed( decimals );

			el.textContent = current;

			if ( t < 1 ) {
				window.requestAnimationFrame( frame );
			} else {
				el.textContent = target.toFixed( decimals );
			}
		}

		// Start from zero so the climb is visible.
		el.textContent = ( 0 ).toFixed( decimals );
		window.requestAnimationFrame( frame );
	}

	/* --------------------------------------------------------------------- *
	 * Reading-progress bar: maps scroll position through the article body to a
	 * 0..1 scaleX on the bar. rAF-throttled, transform-only.
	 * -------------------------------------------------------------------- */
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

	/* --------------------------------------------------------------------- *
	 * Scroll reveals + count-up triggers.
	 * -------------------------------------------------------------------- */
	function initReveals() {
		var nodes = doc.querySelectorAll(
			'[data-orbit-reveal], [data-orbit-stagger]'
		);
		var counters = doc.querySelectorAll( '[data-orbit-count]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything; values already final.
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			for ( i = 0; i < counters.length; i++ ) {
				countUp( counters[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.orbit-motion`, so adding it before we observe keeps the
		// hidden window as small as possible — and if this script had never run,
		// the class would be absent and all content would be visible.
		root.classList.add( 'orbit-motion' );

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];
					if ( entry.isIntersecting ) {
						revealNow( entry.target );

						// Fire any count-up metrics inside the revealed block.
						var nums = entry.target.querySelectorAll( '[data-orbit-count]' );
						for ( var k = 0; k < nums.length; k++ ) {
							countUp( nums[ k ] );
						}

						obs.unobserve( entry.target );
					}
				}
			},
			{
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.12,
			}
		);

		// Accessibility: if focus enters a still-hidden reveal block, reveal it
		// permanently so the focus ring is never on an invisible element.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest(
					'[data-orbit-reveal], [data-orbit-stagger]'
				);
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

		// Catch any count-up element that is not inside an observed reveal block.
		for ( i = 0; i < counters.length; i++ ) {
			if (
				! counters[ i ].closest( '[data-orbit-reveal], [data-orbit-stagger]' )
			) {
				( function ( el ) {
					var solo = new IntersectionObserver(
						function ( entries, obs ) {
							for ( var j = 0; j < entries.length; j++ ) {
								if ( entries[ j ].isIntersecting ) {
									countUp( entries[ j ].target );
									obs.unobserve( entries[ j ].target );
								}
							}
						},
						{ threshold: 0.5 }
					);
					solo.observe( el );
				}( counters[ i ] ) );
			}
		}
	}

	function start() {
		// The starfield and magnetic CTA are decorative enhancements; only wire
		// them up when motion is allowed.
		if ( reduceMotion ) {
			var all = doc.querySelectorAll(
				'[data-orbit-reveal], [data-orbit-stagger]'
			);
			for ( var i = 0; i < all.length; i++ ) {
				revealNow( all[ i ] );
			}
			// Metrics keep their final markup values; no count-up animation.
			initReadingProgress();
			return;
		}

		initStarfield();
		initMagnetic();
		initReveals();
		initReadingProgress();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
