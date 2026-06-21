<?php
/**
 * Atelier Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Ink color
 * controls that update the whole theme live via postMessage, plus
 * selective-refresh partials for the site title and tagline.
 *
 * @package Atelier
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
if ( ! function_exists( 'atelier_color_defaults' ) ) {
	function atelier_color_defaults() {
		return array(
			'atelier_accent' => '#7c3a36', // --at-accent
			'atelier_bg'     => '#f4f1ea', // --at-bg
			'atelier_ink'    => '#1c1b19', // --at-ink
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'atelier_customize_register' ) ) {
	function atelier_customize_register( $wp_customize ) {
		$defaults = atelier_color_defaults();

		$wp_customize->add_section(
			'atelier_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'atelier' ),
				'priority' => 30,
			)
		);

		// Accent color → --at-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'atelier_accent',
			array(
				'default'           => $defaults['atelier_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'atelier_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'atelier' ),
					'description' => esc_html__( 'Links, category kickers, hovers, and accents. Lighter and darker shades follow automatically.', 'atelier' ),
					'section'     => 'atelier_colors',
					'settings'    => 'atelier_accent',
				)
			)
		);

		// Background color → --at-bg (the page canvas / bone paper).
		$wp_customize->add_setting(
			'atelier_bg',
			array(
				'default'           => $defaults['atelier_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'atelier_bg',
				array(
					'label'       => esc_html__( 'Background color', 'atelier' ),
					'description' => esc_html__( 'The paper canvas behind your work.', 'atelier' ),
					'section'     => 'atelier_colors',
					'settings'    => 'atelier_bg',
				)
			)
		);

		// Ink color → --at-ink (headings, buttons, the footer field).
		$wp_customize->add_setting(
			'atelier_ink',
			array(
				'default'           => $defaults['atelier_ink'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'atelier_ink',
				array(
					'label'       => esc_html__( 'Ink color', 'atelier' ),
					'description' => esc_html__( 'Headings, body text, buttons, and the dark footer field.', 'atelier' ),
					'section'     => 'atelier_colors',
					'settings'    => 'atelier_ink',
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
					'render_callback' => 'atelier_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'atelier_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'atelier_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'atelier_customize_partial_blogname' ) ) {
	function atelier_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'atelier_customize_partial_blogdescription' ) ) {
	function atelier_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'atelier_customize_css' ) ) {
	function atelier_customize_css() {
		$defaults = atelier_color_defaults();

		$accent = get_theme_mod( 'atelier_accent', $defaults['atelier_accent'] );
		$bg     = get_theme_mod( 'atelier_bg', $defaults['atelier_bg'] );
		$ink    = get_theme_mod( 'atelier_ink', $defaults['atelier_ink'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['atelier_accent'] ) {
			$vars['--at-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['atelier_bg'] ) {
			$vars['--at-bg'] = $bg;
		}
		if ( $ink && $ink !== $defaults['atelier_ink'] ) {
			$vars['--at-ink'] = $ink;
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
			esc_attr( 'atelier' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'atelier_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'atelier_customize_preview_js' ) ) {
	function atelier_customize_preview_js() {
		wp_enqueue_script(
			'atelier-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'ATELIER_VERSION' ) ? ATELIER_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'atelier_customize_preview_js' );
