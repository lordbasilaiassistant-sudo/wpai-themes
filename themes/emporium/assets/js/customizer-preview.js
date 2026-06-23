/**
 * Emporium — live Customizer preview.
 *
 * Binds each color setting to its CSS custom property so the whole theme — and,
 * via the variable mapping in style.css, the storefront — updates instantly.
 */
( function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.customize ) {
		return;
	}

	var root = document.documentElement;

	function bindColor( settingId, cssVar ) {
		wp.customize( settingId, function ( value ) {
			value.bind( function ( newValue ) {
				if ( newValue ) {
					root.style.setProperty( cssVar, newValue );
				} else {
					root.style.removeProperty( cssVar );
				}
			} );
		} );
	}

	bindColor( 'emporium_accent', '--em-accent' );
	bindColor( 'emporium_bg', '--em-bg' );
	bindColor( 'emporium_ink', '--em-ink' );

	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-title a' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );

	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-description' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );
}() );
