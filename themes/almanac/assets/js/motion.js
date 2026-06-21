/**
 * Almanac — motion system.
 *
 * A calm, garden-like motion layer:
 *   - The SIGNATURE "growing" reveal: blocks rise and gently grow up from the
 *     baseline as they scroll in, as if the note is sprouting on the page.
 *   - The SIGNATURE connective "vine sprout": a tiny stem draws up beside the
 *     intro and single-note titles, then a leaf unfurls.
 *   - A word-by-word reveal on the home intro headline.
 *   - A slim, vine-coloured reading-progress bar on single notes.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately and wire up nothing animated.
 *   - Performance. We animate only transform and opacity (and SVG
 *     stroke-dashoffset for the sprout), observe with a sensible rootMargin,
 *     and unobserve each element after it reveals.
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

		// Word reveals: drop the per-word transforms so wrapping is natural.
		if ( el.hasAttribute( 'data-alm-words' ) ) {
			el.classList.add( 'words-in' );
		}

		// Grow any connective vine sprout that belongs with a revealed block.
		growSproutWithin( el.parentNode || el );
	}

	/**
	 * Kick the sprout grow-in for any sprout that belongs with a revealed
	 * block. The class triggers the CSS stroke-dashoffset transitions.
	 *
	 * @param {Element} scope A container to search within.
	 */
	function growSproutWithin( scope ) {
		if ( ! scope || ! scope.querySelectorAll ) {
			return;
		}
		var sprouts = scope.querySelectorAll( '.alm-sprout' );
		for ( var i = 0; i < sprouts.length; i++ ) {
			sprouts[ i ].classList.add( 'is-grown' );
		}
	}

	/**
	 * Split a headline's text into per-word spans so each word can rise in.
	 * We only touch the dedicated text span to avoid disturbing links or the
	 * decorative SVG sitting beside it. Whitespace is preserved so the line
	 * still wraps and reads identically to a screen reader.
	 *
	 * @param {Element} host The [data-alm-words] element.
	 * @return {boolean} Whether a split was performed.
	 */
	function splitWords( host ) {
		var target = host.querySelector(
			'.garden-intro__text, .entry-title__text'
		);

		// Fall back to the host itself only if it has no element children
		// (pure text), so we never blow away nested markup like links.
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
			word.className = 'alm-word';
			word.textContent = token;
			// Inline custom property staggers each word; clamped so very long
			// headlines never crawl in for an uncomfortable amount of time.
			word.style.setProperty( '--w-delay', delay.toFixed( 2 ) + 's' );
			delay = Math.min( delay + 0.05, 0.7 );
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
			// Distance the article top travels from first-visible to fully read.
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
	 * Wire up all scroll reveals.
	 */
	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-alm-reveal]' );
		var words = doc.querySelectorAll( '[data-alm-words]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything. Split the headlines
			// (purely cosmetic) and leave every block in its visible end-state.
			for ( i = 0; i < words.length; i++ ) {
				splitWords( words[ i ] );
			}
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			for ( i = 0; i < words.length; i++ ) {
				revealNow( words[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.almanac-motion`, so adding it before we split or observe
		// keeps the hidden window as small as possible — and if this script had
		// never run, the class would be absent and all content would be visible.
		root.classList.add( 'almanac-motion' );

		// Prepare word headlines (split once, up front, before they reveal).
		for ( i = 0; i < words.length; i++ ) {
			if ( splitWords( words[ i ] ) ) {
				words[ i ].classList.add( 'words-ready' );
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
				// Reveal a touch before the element reaches the fold, and only
				// once a sliver is on screen — feels responsive, never abrupt.
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.08,
			}
		);

		// Accessibility: if focus moves into a still-hidden reveal block (e.g. a
		// keyboard user tabs to an off-screen "Read the note" link before it has
		// scrolled in), reveal it permanently and stop observing it so the focus
		// ring is never sitting on an invisible element.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-alm-reveal], [data-alm-words]' );
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

		// Word headlines reveal on their own observer so the stagger fires the
		// moment they enter, independent of any wrapping reveal block.
		for ( i = 0; i < words.length; i++ ) {
			// A words element may also carry data-alm-reveal (handled above);
			// only observe standalone ones here to avoid a double reveal.
			if ( ! words[ i ].hasAttribute( 'data-alm-reveal' ) ) {
				observer.observe( words[ i ] );
			}
		}
	}

	function start() {
		// Reduced motion (or no JS support for the basics): reveal all, wire
		// nothing animated. Reading progress is informational, so we still show
		// it but it jumps rather than eases (CSS disables its transition).
		if ( reduceMotion ) {
			var all = doc.querySelectorAll(
				'[data-alm-reveal], [data-alm-words]'
			);
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
