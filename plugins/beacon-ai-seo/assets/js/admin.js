/**
 * Beacon — AI & SEO · settings-page enhancement.
 *
 * Pure progressive enhancement: the /llms.txt link is a working anchor without
 * any JavaScript. This file adds a one-click "copy URL" affordance and a polite
 * status message. It has no dependencies, makes no network calls, and respects
 * prefers-reduced-motion (it simply skips the confirmation nudge).
 *
 * @package BeaconAiSeo
 */
( function () {
	'use strict';

	var L10N = window.beaconAdmin || { copied: 'Copied!', copy: 'Copy' };

	/**
	 * Whether the visitor has asked for reduced motion.
	 *
	 * @return {boolean}
	 */
	function prefersReducedMotion() {
		return (
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
		);
	}

	/**
	 * Copy text to the clipboard, with a legacy fallback.
	 *
	 * Returns a promise-like that resolves true on success, false otherwise.
	 *
	 * @param {string} text The text to copy.
	 * @return {Promise<boolean>}
	 */
	function copyText( text ) {
		if ( navigator.clipboard && typeof navigator.clipboard.writeText === 'function' ) {
			return navigator.clipboard.writeText( text ).then(
				function () {
					return true;
				},
				function () {
					return legacyCopy( text );
				}
			);
		}

		return Promise.resolve( legacyCopy( text ) );
	}

	/**
	 * execCommand-based fallback for older browsers.
	 *
	 * @param {string} text The text to copy.
	 * @return {boolean} Whether the copy succeeded.
	 */
	function legacyCopy( text ) {
		var area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', '' );
		area.style.position = 'absolute';
		area.style.left = '-9999px';
		document.body.appendChild( area );

		var ok = false;
		try {
			area.select();
			ok = document.execCommand( 'copy' );
		} catch ( e ) {
			ok = false;
		}

		document.body.removeChild( area );
		return ok;
	}

	/**
	 * Show a transient status message in the live region.
	 *
	 * @param {Element} region  The aria-live status element.
	 * @param {string}  message The message to announce.
	 */
	function announce( region, message ) {
		if ( ! region ) {
			return;
		}

		region.textContent = message;
		region.classList.add( 'is-visible' );

		window.clearTimeout( region._beaconTimer );
		region._beaconTimer = window.setTimeout( function () {
			region.classList.remove( 'is-visible' );
		}, 2400 );
	}

	/**
	 * Wire up the copy button.
	 */
	function init() {
		var button = document.querySelector( '[data-beacon-copy]' );

		if ( ! button ) {
			return;
		}

		var status = document.querySelector( '.beacon-status' );
		var url = button.getAttribute( 'data-beacon-copy' ) || '';

		button.addEventListener( 'click', function () {
			copyText( url ).then( function ( ok ) {
				if ( ! ok ) {
					return;
				}

				announce( status, L10N.copied );

				if ( ! prefersReducedMotion() ) {
					button.classList.add( 'is-copied' );
					window.setTimeout( function () {
						button.classList.remove( 'is-copied' );
					}, 220 );
				}
			} );
		} );
	}

	try {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
	} catch ( e ) {
		/* Enhancement only — the anchor still works without this. */
	}
} )();
