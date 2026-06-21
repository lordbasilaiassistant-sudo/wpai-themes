/**
 * Almanac — live Customizer preview.
 *
 * Binds each color setting to its real CSS custom property so the whole theme
 * (links, tags, threads, the dot grid, surfaces) updates instantly, and
 * refreshes the site title/tagline text.
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

	// Accent: derived deep/soft/wash/seed shades follow via color-mix() in CSS.
	bindColor( 'almanac_accent', '--alm-accent' );
	bindColor( 'almanac_bg', '--alm-bg' );
	bindColor( 'almanac_surface', '--alm-surface' );

	// Site title.
	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-title a' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );

	// Site tagline.
	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-description' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );
}() );
