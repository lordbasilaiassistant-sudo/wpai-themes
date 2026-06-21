<?php
/**
 * Almanac Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Surface color
 * controls that update the whole theme live via postMessage, plus
 * selective-refresh partials for the site title and tagline.
 *
 * @package Almanac
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
if ( ! function_exists( 'almanac_color_defaults' ) ) {
	function almanac_color_defaults() {
		return array(
			'almanac_accent'  => '#2f7d6e', // --alm-accent
			'almanac_bg'      => '#f4f1e9', // --alm-bg
			'almanac_surface' => '#fbf9f3', // --alm-surface
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'almanac_customize_register' ) ) {
	function almanac_customize_register( $wp_customize ) {
		$defaults = almanac_color_defaults();

		$wp_customize->add_section(
			'almanac_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'almanac' ),
				'priority' => 30,
			)
		);

		// Accent color → --alm-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'almanac_accent',
			array(
				'default'           => $defaults['almanac_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'almanac_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'almanac' ),
					'description' => esc_html__( 'Links, tags, category patches, connective threads, and the sprout. Lighter and darker shades follow automatically.', 'almanac' ),
					'section'     => 'almanac_colors',
					'settings'    => 'almanac_accent',
				)
			)
		);

		// Background color → --alm-bg (the paper canvas with the dot grid).
		$wp_customize->add_setting(
			'almanac_bg',
			array(
				'default'           => $defaults['almanac_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'almanac_bg',
				array(
					'label'       => esc_html__( 'Background color', 'almanac' ),
					'description' => esc_html__( 'The paper canvas behind your notes (the faint dot grid is drawn over it).', 'almanac' ),
					'section'     => 'almanac_colors',
					'settings'    => 'almanac_bg',
				)
			)
		);

		// Surface color → --alm-surface (cards, header, sidebar stacks).
		$wp_customize->add_setting(
			'almanac_surface',
			array(
				'default'           => $defaults['almanac_surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'almanac_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'almanac' ),
					'description' => esc_html__( 'Cards, the header, tags, and the sidebar stacks that sit above the paper.', 'almanac' ),
					'section'     => 'almanac_colors',
					'settings'    => 'almanac_surface',
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
					'render_callback' => 'almanac_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'almanac_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'almanac_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'almanac_customize_partial_blogname' ) ) {
	function almanac_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'almanac_customize_partial_blogdescription' ) ) {
	function almanac_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'almanac_customize_css' ) ) {
	function almanac_customize_css() {
		$defaults = almanac_color_defaults();

		$accent  = get_theme_mod( 'almanac_accent', $defaults['almanac_accent'] );
		$bg      = get_theme_mod( 'almanac_bg', $defaults['almanac_bg'] );
		$surface = get_theme_mod( 'almanac_surface', $defaults['almanac_surface'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['almanac_accent'] ) {
			$vars['--alm-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['almanac_bg'] ) {
			$vars['--alm-bg'] = $bg;
		}
		if ( $surface && $surface !== $defaults['almanac_surface'] ) {
			$vars['--alm-surface'] = $surface;
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
			esc_attr( 'almanac' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'almanac_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'almanac_customize_preview_js' ) ) {
	function almanac_customize_preview_js() {
		wp_enqueue_script(
			'almanac-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'ALMANAC_VERSION' ) ? ALMANAC_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'almanac_customize_preview_js' );
