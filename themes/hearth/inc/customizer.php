<?php
/**
 * Hearth Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent (terracotta), Background (toasted
 * cream), and Olive (the open/hours signal) color controls that update the
 * whole theme live via postMessage, plus selective-refresh partials for the
 * site title and tagline.
 *
 * @package Hearth
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
if ( ! function_exists( 'hearth_color_defaults' ) ) {
	function hearth_color_defaults() {
		return array(
			'hearth_accent' => '#c2541f', // --h-accent
			'hearth_bg'     => '#f6ede0', // --h-bg
			'hearth_olive'  => '#5f6b3a', // --h-olive
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'hearth_customize_register' ) ) {
	function hearth_customize_register( $wp_customize ) {
		$defaults = hearth_color_defaults();

		$wp_customize->add_section(
			'hearth_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'hearth' ),
				'priority' => 30,
			)
		);

		// Accent color → --h-accent (derived shades follow via color-mix).
		$wp_customize->add_setting(
			'hearth_accent',
			array(
				'default'           => $defaults['hearth_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'hearth_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'hearth' ),
					'description' => esc_html__( 'Buttons, links, headings rules, and CTAs. Lighter and darker shades follow automatically.', 'hearth' ),
					'section'     => 'hearth_colors',
					'settings'    => 'hearth_accent',
				)
			)
		);

		// Background color → --h-bg (the page canvas).
		$wp_customize->add_setting(
			'hearth_bg',
			array(
				'default'           => $defaults['hearth_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'hearth_bg',
				array(
					'label'       => esc_html__( 'Background color', 'hearth' ),
					'description' => esc_html__( 'The warm canvas behind your content.', 'hearth' ),
					'section'     => 'hearth_colors',
					'settings'    => 'hearth_bg',
				)
			)
		);

		// Olive color → --h-olive (the open/hours signal + category pills).
		$wp_customize->add_setting(
			'hearth_olive',
			array(
				'default'           => $defaults['hearth_olive'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'hearth_olive',
				array(
					'label'       => esc_html__( 'Herb / open color', 'hearth' ),
					'description' => esc_html__( 'The "open now" signal in the hours card and category pills. Its shades follow automatically.', 'hearth' ),
					'section'     => 'hearth_colors',
					'settings'    => 'hearth_olive',
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
					'render_callback' => 'hearth_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'hearth_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'hearth_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'hearth_customize_partial_blogname' ) ) {
	function hearth_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'hearth_customize_partial_blogdescription' ) ) {
	function hearth_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'hearth_customize_css' ) ) {
	function hearth_customize_css() {
		$defaults = hearth_color_defaults();

		$accent = get_theme_mod( 'hearth_accent', $defaults['hearth_accent'] );
		$bg     = get_theme_mod( 'hearth_bg', $defaults['hearth_bg'] );
		$olive  = get_theme_mod( 'hearth_olive', $defaults['hearth_olive'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['hearth_accent'] ) {
			$vars['--h-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['hearth_bg'] ) {
			$vars['--h-bg'] = $bg;
		}
		if ( $olive && $olive !== $defaults['hearth_olive'] ) {
			$vars['--h-olive'] = $olive;
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
			esc_attr( 'hearth' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'hearth_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'hearth_customize_preview_js' ) ) {
	function hearth_customize_preview_js() {
		wp_enqueue_script(
			'hearth-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'HEARTH_VERSION' ) ? HEARTH_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'hearth_customize_preview_js' );
