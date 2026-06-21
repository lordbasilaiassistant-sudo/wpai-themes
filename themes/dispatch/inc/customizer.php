<?php
/**
 * Dispatch Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Bar
 * color controls that update the whole theme live via postMessage, plus
 * selective-refresh partials for the site title and tagline.
 *
 * @package Dispatch
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
if ( ! function_exists( 'dispatch_color_defaults' ) ) {
	function dispatch_color_defaults() {
		return array(
			'dispatch_accent' => '#d6202b', // --d-accent
			'dispatch_bg'     => '#f4f5f7', // --d-bg
			'dispatch_bar'    => '#16181d', // --d-bar (breaking bar / footer)
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'dispatch_customize_register' ) ) {
	function dispatch_customize_register( $wp_customize ) {
		$defaults = dispatch_color_defaults();

		$wp_customize->add_section(
			'dispatch_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'dispatch' ),
				'priority' => 30,
			)
		);

		// Accent color → --d-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'dispatch_accent',
			array(
				'default'           => $defaults['dispatch_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'dispatch_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'dispatch' ),
					'description' => esc_html__( 'Links, buttons, the ticker tag, the default category color, and accents. Lighter and darker shades follow automatically.', 'dispatch' ),
					'section'     => 'dispatch_colors',
					'settings'    => 'dispatch_accent',
				)
			)
		);

		// Background color → --d-bg (the page canvas).
		$wp_customize->add_setting(
			'dispatch_bg',
			array(
				'default'           => $defaults['dispatch_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'dispatch_bg',
				array(
					'label'       => esc_html__( 'Background color', 'dispatch' ),
					'description' => esc_html__( 'The page canvas behind your content and cards.', 'dispatch' ),
					'section'     => 'dispatch_colors',
					'settings'    => 'dispatch_bg',
				)
			)
		);

		// Bar color → --d-bar (the breaking bar and the footer).
		$wp_customize->add_setting(
			'dispatch_bar',
			array(
				'default'           => $defaults['dispatch_bar'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'dispatch_bar',
				array(
					'label'       => esc_html__( 'Bar color', 'dispatch' ),
					'description' => esc_html__( 'The thin breaking bar at the top of the page and the footer.', 'dispatch' ),
					'section'     => 'dispatch_colors',
					'settings'    => 'dispatch_bar',
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
					'render_callback' => 'dispatch_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'dispatch_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'dispatch_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'dispatch_customize_partial_blogname' ) ) {
	function dispatch_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'dispatch_customize_partial_blogdescription' ) ) {
	function dispatch_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'dispatch_customize_css' ) ) {
	function dispatch_customize_css() {
		$defaults = dispatch_color_defaults();

		$accent = get_theme_mod( 'dispatch_accent', $defaults['dispatch_accent'] );
		$bg     = get_theme_mod( 'dispatch_bg', $defaults['dispatch_bg'] );
		$bar    = get_theme_mod( 'dispatch_bar', $defaults['dispatch_bar'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['dispatch_accent'] ) {
			$vars['--d-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['dispatch_bg'] ) {
			$vars['--d-bg'] = $bg;
		}
		if ( $bar && $bar !== $defaults['dispatch_bar'] ) {
			$vars['--d-bar'] = $bar;
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
			esc_attr( 'dispatch' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'dispatch_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'dispatch_customize_preview_js' ) ) {
	function dispatch_customize_preview_js() {
		wp_enqueue_script(
			'dispatch-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'DISPATCH_VERSION' ) ? DISPATCH_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'dispatch_customize_preview_js' );
