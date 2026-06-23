/**
 * Emporium — header behaviour.
 *
 * Progressive enhancement only: the mobile menu toggle, the slide-down search
 * panel, and a "stuck" class on the sticky header once the page scrolls. The
 * site is fully usable without any of this.
 */
( function () {
	'use strict';

	var header = document.getElementById( 'site-header' );
	var nav = document.querySelector( '.main-nav' );
	var toggle = document.querySelector( '.nav-toggle' );
	var searchBtn = document.querySelector( '.site-actions__search' );
	var searchPanel = document.getElementById( 'em-search' );

	/* ---- mobile menu ---- */
	if ( nav && toggle ) {
		var menu = nav.querySelector( '.main-nav__list' );

		function setNav( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			nav.classList.toggle( 'is-open', open );
		}

		toggle.addEventListener( 'click', function () {
			setNav( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		if ( menu ) {
			menu.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( 'a' ) ) {
					setNav( false );
				}
			} );
		}

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && nav.classList.contains( 'is-open' ) ) {
				setNav( false );
				toggle.focus();
			}
		} );
	}

	/* ---- search panel ---- */
	if ( searchBtn && searchPanel ) {
		searchBtn.addEventListener( 'click', function () {
			var open = searchPanel.classList.toggle( 'is-open' );
			searchBtn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				var field = searchPanel.querySelector( '.search-field' );
				if ( field ) {
					field.focus();
				}
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && searchPanel.classList.contains( 'is-open' ) ) {
				searchPanel.classList.remove( 'is-open' );
				searchBtn.setAttribute( 'aria-expanded', 'false' );
				searchBtn.focus();
			}
		} );
	}

	/* ---- sticky-header shadow ---- */
	if ( header ) {
		var ticking = false;
		function update() {
			header.classList.toggle( 'is-stuck', window.scrollY > 8 );
			ticking = false;
		}
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );
		update();
	}
}() );
