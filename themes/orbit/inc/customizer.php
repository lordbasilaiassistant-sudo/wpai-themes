<?php
/**
 * Orbit Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Surface
 * color controls that update the whole theme live via postMessage, plus
 * selective-refresh partials for the site title and tagline.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values, mapped to the real CSS custom properties in style.css.
 * Keeping these in one place lets the front-end printer skip any value that
 * still matches its default, so the polished defaults render identically.
 *
 * @return array
 */
if ( ! function_exists( 'orbit_color_defaults' ) ) {
	function orbit_color_defaults() {
		return array(
			'orbit_accent'  => '#2ee6c8', // --o-accent
			'orbit_bg'      => '#070b16', // --o-bg
			'orbit_surface' => '#0f172a', // --o-surface
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'orbit_customize_register' ) ) {
	function orbit_customize_register( $wp_customize ) {
		$defaults = orbit_color_defaults();

		$wp_customize->add_section(
			'orbit_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'orbit' ),
				'priority' => 30,
			)
		);

		// Accent color → --o-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'orbit_accent',
			array(
				'default'           => $defaults['orbit_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'orbit_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'orbit' ),
					'description' => esc_html__( 'The neon accent on links, buttons, metrics, and the hero. Lighter and darker shades follow automatically.', 'orbit' ),
					'section'     => 'orbit_colors',
					'settings'    => 'orbit_accent',
				)
			)
		);

		// Background color → --o-bg (the page canvas).
		$wp_customize->add_setting(
			'orbit_bg',
			array(
				'default'           => $defaults['orbit_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'orbit_bg',
				array(
					'label'       => esc_html__( 'Background color', 'orbit' ),
					'description' => esc_html__( 'The deep space canvas behind your content.', 'orbit' ),
					'section'     => 'orbit_colors',
					'settings'    => 'orbit_bg',
				)
			)
		);

		// Surface color → --o-surface (cards, header, sidebar panels).
		$wp_customize->add_setting(
			'orbit_surface',
			array(
				'default'           => $defaults['orbit_surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'orbit_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'orbit' ),
					'description' => esc_html__( 'Cards, the header, and sidebar panels that sit above the background.', 'orbit' ),
					'section'     => 'orbit_colors',
					'settings'    => 'orbit_surface',
				)
			)
		);

		// Live-update the site title and tagline via selective refresh.
		if ( isset( $wp_customize->selective_refresh ) ) {
			$blogname = $wp_customize->get_setting( 'blogname' );
			if ( $blogname ) {
				$blogname->transport = 'postMessage';
			}
			$blogdescription = $wp_customize->get_setting( 'blogdescription' );
			if ( $blogdescription ) {
				$blogdescription->transport = 'postMessage';
			}

			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title a',
					'render_callback' => 'orbit_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'orbit_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'orbit_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'orbit_customize_partial_blogname' ) ) {
	function orbit_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'orbit_customize_partial_blogdescription' ) ) {
	function orbit_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'orbit_customize_css' ) ) {
	function orbit_customize_css() {
		$defaults = orbit_color_defaults();

		$accent  = get_theme_mod( 'orbit_accent', $defaults['orbit_accent'] );
		$bg      = get_theme_mod( 'orbit_bg', $defaults['orbit_bg'] );
		$surface = get_theme_mod( 'orbit_surface', $defaults['orbit_surface'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['orbit_accent'] ) {
			$vars['--o-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['orbit_bg'] ) {
			$vars['--o-bg'] = $bg;
		}
		if ( $surface && $surface !== $defaults['orbit_surface'] ) {
			$vars['--o-surface'] = $surface;
		}

		if ( empty( $vars ) ) {
			return;
		}

		$rules = '';
		foreach ( $vars as $name => $value ) {
			$rules .= $name . ':' . $value . ';';
		}

		printf(
			'<style id="%1$s-customize">:root{%2$s}</style>' . "\n",
			esc_attr( 'orbit' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'orbit_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'orbit_customize_preview_js' ) ) {
	function orbit_customize_preview_js() {
		wp_enqueue_script(
			'orbit-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'ORBIT_VERSION' ) ? ORBIT_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'orbit_customize_preview_js' );
