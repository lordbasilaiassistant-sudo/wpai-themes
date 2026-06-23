<?php
/**
 * Emporium Customizer: live color controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Ink controls.
 * Because style.css maps the store's Till variables onto Emporium's, the Ink
 * control also recolours the storefront's buttons and cart, keeping theme and
 * shop in sync from a single set of controls.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'emporium_color_defaults' ) ) {
	/**
	 * Defaults, mapped to the CSS custom properties in style.css.
	 *
	 * @return array
	 */
	function emporium_color_defaults() {
		return array(
			'emporium_accent' => '#1f5d44', // --em-accent
			'emporium_bg'     => '#faf8f3', // --em-bg
			'emporium_ink'    => '#16150f', // --em-ink
		);
	}
}

if ( ! function_exists( 'emporium_customize_register' ) ) {
	/**
	 * Register section, settings, controls, and partials.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager.
	 */
	function emporium_customize_register( $wp_customize ) {
		$defaults = emporium_color_defaults();

		$wp_customize->add_section(
			'emporium_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'emporium' ),
				'priority' => 30,
			)
		);

		$controls = array(
			'emporium_accent' => array(
				'label' => esc_html__( 'Accent color', 'emporium' ),
				'desc'  => esc_html__( 'Links, category kickers, and active states.', 'emporium' ),
			),
			'emporium_bg'     => array(
				'label' => esc_html__( 'Background color', 'emporium' ),
				'desc'  => esc_html__( 'The page canvas behind your store.', 'emporium' ),
			),
			'emporium_ink'    => array(
				'label' => esc_html__( 'Ink color', 'emporium' ),
				'desc'  => esc_html__( 'Headings, text, the footer, and the storefront buttons & cart.', 'emporium' ),
			),
		);

		foreach ( $controls as $id => $meta ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $defaults[ $id ],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$id,
					array(
						'label'       => $meta['label'],
						'description' => $meta['desc'],
						'section'     => 'emporium_colors',
						'settings'    => $id,
					)
				)
			);
		}

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
					'render_callback' => 'emporium_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'emporium_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'emporium_customize_register' );

if ( ! function_exists( 'emporium_customize_partial_blogname' ) ) {
	/**
	 * Render the site title partial.
	 *
	 * @return string
	 */
	function emporium_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

if ( ! function_exists( 'emporium_customize_partial_blogdescription' ) ) {
	/**
	 * Render the tagline partial.
	 *
	 * @return string
	 */
	function emporium_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

if ( ! function_exists( 'emporium_customize_css' ) ) {
	/**
	 * Print the chosen colors as CSS custom properties. Only emits a variable
	 * that differs from its default, so an untouched site renders identically.
	 */
	function emporium_customize_css() {
		$defaults = emporium_color_defaults();

		$map = array(
			'emporium_accent' => '--em-accent',
			'emporium_bg'     => '--em-bg',
			'emporium_ink'    => '--em-ink',
		);

		$rules = '';
		foreach ( $map as $mod => $var ) {
			$value = get_theme_mod( $mod, $defaults[ $mod ] );
			if ( $value && $value !== $defaults[ $mod ] ) {
				$rules .= $var . ':' . $value . ';';
			}
		}

		if ( '' === $rules ) {
			return;
		}

		printf(
			'<style id="emporium-customize">:root{%s}</style>' . "\n",
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'emporium_customize_css', 20 );

if ( ! function_exists( 'emporium_customize_preview_js' ) ) {
	/**
	 * Enqueue the live-preview script in the Customizer frame.
	 */
	function emporium_customize_preview_js() {
		wp_enqueue_script(
			'emporium-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'EMPORIUM_VERSION' ) ? EMPORIUM_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'emporium_customize_preview_js' );
