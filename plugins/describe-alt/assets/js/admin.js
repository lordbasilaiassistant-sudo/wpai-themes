/**
 * Describe — Auto Alt Text: status-page enhancement.
 *
 * Progressive enhancement only. The coverage gauge is fully readable without
 * this file — the percentage is rendered server-side and the ring is drawn in
 * CSS. Here we add a brief count-up of the number and sweep of the ring when the
 * page loads, for a touch of life on the otherwise static panel.
 *
 * Constraints (kept in lockstep with the CSS):
 * - Respect prefers-reduced-motion: if set (or matchMedia is missing), do
 *   nothing at all — the final value is already on screen.
 * - Touch only opacity/text content and a single CSS custom property
 *   (--describe-alt-percent); never layout properties — no jank, no CLS.
 * - No dependencies, no globals, no console noise; fail safe to the static view.
 *
 * @package DescribeAlt
 */
( function () {
	'use strict';

	/**
	 * Animate the gauge ring + number from 0 to its final value.
	 *
	 * @param {Element} meter The .describe-alt-meter element.
	 */
	function animateGauge( meter ) {
		var valueEl = meter.querySelector( '.describe-alt-meter__value' );
		if ( ! valueEl ) {
			return;
		}

		// The server-rendered final percentage (the static, correct value).
		var target = parseInt( meter.style.getPropertyValue( '--describe-alt-percent' ), 10 );
		if ( isNaN( target ) || target <= 0 ) {
			return;
		}

		// Preserve the unit span ("%") and only update the leading number node.
		var unit = valueEl.querySelector( '.describe-alt-meter__unit' );
		var unitHTML = unit ? unit.outerHTML : '';

		var duration = 700; // ms
		var start = null;

		// Reset to zero, then sweep up via requestAnimationFrame.
		meter.style.setProperty( '--describe-alt-percent', '0' );

		function frame( ts ) {
			if ( null === start ) {
				start = ts;
			}
			var t = Math.min( 1, ( ts - start ) / duration );
			// easeOutCubic for a gentle settle.
			var eased = 1 - Math.pow( 1 - t, 3 );
			var current = Math.round( eased * target );

			meter.style.setProperty( '--describe-alt-percent', String( current ) );
			valueEl.firstChild.nodeValue = String( current );

			if ( t < 1 ) {
				window.requestAnimationFrame( frame );
			} else {
				// Snap exactly to target and restore markup.
				meter.style.setProperty( '--describe-alt-percent', String( target ) );
				valueEl.innerHTML = String( target ) + unitHTML;
			}
		}

		window.requestAnimationFrame( frame );
	}

	/**
	 * Wire up the enhancement once the DOM is ready.
	 */
	function init() {
		var meter = document.querySelector( '.describe-alt-meter' );
		if ( ! meter ) {
			return;
		}

		// Honor the user's motion preference (or bail if matchMedia is missing).
		var prefersReduced =
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( prefersReduced || typeof window.requestAnimationFrame !== 'function' ) {
			return; // Static value is already correct on screen.
		}

		animateGauge( meter );
	}

	try {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
	} catch ( e ) {
		// No-op: the static, server-rendered value remains visible.
	}
} )();
