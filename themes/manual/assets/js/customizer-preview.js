/**
 * Manual — live Customizer preview.
 *
 * Binds each color setting to its real CSS custom property so the whole theme
 * updates instantly, refreshes the version chip, and the site title/tagline.
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

	// Accent: derived deep/soft/wash shades follow via color-mix() in CSS.
	bindColor( 'manual_accent', '--m-accent' );
	bindColor( 'manual_bg', '--m-bg' );
	bindColor( 'manual_surface', '--m-surface' );

	// Documentation version chip.
	wp.customize( 'manual_version_label', function ( value ) {
		value.bind( function ( newValue ) {
			var chip = document.querySelector( '.site-version' );
			var label = ( newValue || '' ).trim();

			if ( chip ) {
				if ( label ) {
					chip.textContent = label;
					chip.hidden = false;
				} else {
					chip.hidden = true;
				}
			} else if ( label ) {
				// No chip rendered yet (was empty on load): create one so the
				// preview reflects the new value without a full refresh.
				var title = document.querySelector( '.site-title' );
				if ( title ) {
					chip = document.createElement( 'span' );
					chip.className = 'site-version';
					chip.textContent = label;
					title.appendChild( chip );
				}
			}
		} );
	} );

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
