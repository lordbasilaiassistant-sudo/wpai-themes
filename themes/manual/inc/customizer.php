<?php
/**
 * Manual Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Surface color
 * controls that update the whole theme live via postMessage, a documentation
 * version label, and selective-refresh partials for the site title and tagline.
 *
 * @package Manual
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
if ( ! function_exists( 'manual_color_defaults' ) ) {
	function manual_color_defaults() {
		return array(
			'manual_accent'  => '#1f7a8c', // --m-accent
			'manual_bg'      => '#f6f8fa', // --m-bg
			'manual_surface' => '#ffffff', // --m-surface
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'manual_customize_register' ) ) {
	function manual_customize_register( $wp_customize ) {
		$defaults = manual_color_defaults();

		$wp_customize->add_section(
			'manual_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'manual' ),
				'priority' => 30,
			)
		);

		// Accent color → --m-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'manual_accent',
			array(
				'default'           => $defaults['manual_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'manual_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'manual' ),
					'description' => esc_html__( 'Links, buttons, the version chip, code accents, and active navigation. Lighter and darker shades follow automatically.', 'manual' ),
					'section'     => 'manual_colors',
					'settings'    => 'manual_accent',
				)
			)
		);

		// Background color → --m-bg (the page canvas).
		$wp_customize->add_setting(
			'manual_bg',
			array(
				'default'           => $defaults['manual_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'manual_bg',
				array(
					'label'       => esc_html__( 'Background color', 'manual' ),
					'description' => esc_html__( 'The page canvas behind your documentation.', 'manual' ),
					'section'     => 'manual_colors',
					'settings'    => 'manual_bg',
				)
			)
		);

		// Surface color → --m-surface (the header, cards, nav rail, footer).
		$wp_customize->add_setting(
			'manual_surface',
			array(
				'default'           => $defaults['manual_surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'manual_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'manual' ),
					'description' => esc_html__( 'The sticky header, the docs navigation rail, cards, and the footer.', 'manual' ),
					'section'     => 'manual_colors',
					'settings'    => 'manual_surface',
				)
			)
		);

		// Documentation version label (text). A signature of a docs site.
		$wp_customize->add_setting(
			'manual_version_label',
			array(
				'default'           => 'v1.0',
				'sanitize_callback' => 'manual_sanitize_version_label',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'manual_version_label',
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Version label', 'manual' ),
				'description' => esc_html__( 'The small chip shown beside your site title (e.g. “v2.4” or “stable”). Leave blank to hide it.', 'manual' ),
				'section'     => 'manual_colors',
				'settings'    => 'manual_version_label',
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
					'render_callback' => 'manual_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'manual_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'manual_customize_register' );

/**
 * Sanitize the version-label text: a short, single-line string.
 *
 * @param string $value Raw value.
 * @return string
 */
if ( ! function_exists( 'manual_sanitize_version_label' ) ) {
	function manual_sanitize_version_label( $value ) {
		$value = sanitize_text_field( (string) $value );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, 24 );
		}
		return substr( $value, 0, 24 );
	}
}

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'manual_customize_partial_blogname' ) ) {
	function manual_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'manual_customize_partial_blogdescription' ) ) {
	function manual_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'manual_customize_css' ) ) {
	function manual_customize_css() {
		$defaults = manual_color_defaults();

		$accent  = get_theme_mod( 'manual_accent', $defaults['manual_accent'] );
		$bg      = get_theme_mod( 'manual_bg', $defaults['manual_bg'] );
		$surface = get_theme_mod( 'manual_surface', $defaults['manual_surface'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['manual_accent'] ) {
			$vars['--m-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['manual_bg'] ) {
			$vars['--m-bg'] = $bg;
		}
		if ( $surface && $surface !== $defaults['manual_surface'] ) {
			$vars['--m-surface'] = $surface;
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
			esc_attr( 'manual' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'manual_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'manual_customize_preview_js' ) ) {
	function manual_customize_preview_js() {
		wp_enqueue_script(
			'manual-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'MANUAL_VERSION' ) ? MANUAL_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'manual_customize_preview_js' );
