<?php
/**
 * Shortcodes and template tags for Shipped — Auto Changelog & Roadmap.
 *
 * Public surface:
 *   [shipped_changelog]  — vertical release timeline.
 *   [shipped_roadmap]    — Planned / In progress / Shipped status board.
 *   shipped_changelog()  — template tag (echoes the timeline).
 *   shipped_roadmap()    — template tag (echoes the board).
 *
 * Both shortcodes are zero-config: with no attributes and no inner content they
 * source themselves from the watched categories. Authors may override the
 * category/heading per instance, toggle JSON-LD, and/or supply manual entries
 * as inner content (see shipped_parse_inner_content for the convention).
 *
 * @package Shipped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Ensure the plugin's assets are enqueued because a section just rendered.
 *
 * The content sniff (shipped_maybe_enqueue_for_content) handles in-content
 * shortcodes for the zero-flash head path; this on-demand call covers every
 * other placement — a template tag, do_shortcode in a theme, a widget, or a
 * block we can't pre-detect — so the CSS/JS load only on pages that actually
 * contain a timeline or board, never site-wide.
 *
 * WordPress prints styles/scripts enqueued after wp_head in the footer, so a
 * call any time before wp_footer fires still loads the assets; once the footer
 * has run it's too late, hence the guard. Safe to call repeatedly — wp_enqueue_*
 * de-dupes by handle, and the no-JS reveal default keeps everything visible even
 * in the rare too-late case.
 *
 * @return void
 */
function shipped_flag_used() {
	if ( ! did_action( 'wp_footer' ) ) {
		shipped_enqueue_assets_now();
	}
}

/**
 * Shared attribute parsing for both shortcodes.
 *
 * @param array  $atts     Raw shortcode attributes.
 * @param string $mode     'changelog' or 'roadmap'.
 * @param string $default_heading Default heading for this mode.
 * @return array{category:string,heading:string,schema:bool}
 */
function shipped_parse_atts( $atts, $mode, $default_heading ) {
	$default_category = 'roadmap' === $mode ? shipped_roadmap_category() : shipped_changelog_category();

	$atts = shortcode_atts(
		array(
			'category' => $default_category,
			'heading'  => $default_heading,
			'schema'   => 'changelog' === $mode ? 'true' : 'false',
		),
		is_array( $atts ) ? $atts : array(),
		'shipped_' . $mode
	);

	return array(
		'category' => sanitize_text_field( (string) $atts['category'] ),
		'heading'  => sanitize_text_field( (string) $atts['heading'] ),
		'schema'   => in_array( strtolower( (string) $atts['schema'] ), array( 'true', '1', 'yes', 'on' ), true ),
	);
}

/**
 * Shortcode handler: [shipped_changelog].
 *
 * @param array       $atts    Shortcode attributes (all optional).
 * @param string|null $content Optional manual inner content.
 * @return string Section HTML.
 */
function shipped_changelog_shortcode( $atts, $content = null ) {
	$config = shipped_parse_atts( $atts, 'changelog', __( 'Changelog', 'shipped-changelog' ) );
	$rows   = shipped_parse_inner_content( (string) $content, 'changelog' );

	shipped_flag_used();

	return shipped_render_changelog( $config['category'], $rows, $config['heading'], $config['schema'] );
}
add_shortcode( 'shipped_changelog', 'shipped_changelog_shortcode' );

/**
 * Shortcode handler: [shipped_roadmap].
 *
 * @param array       $atts    Shortcode attributes (all optional).
 * @param string|null $content Optional manual inner content.
 * @return string Section HTML.
 */
function shipped_roadmap_shortcode( $atts, $content = null ) {
	$config = shipped_parse_atts( $atts, 'roadmap', __( 'Roadmap', 'shipped-changelog' ) );
	$rows   = shipped_parse_inner_content( (string) $content, 'roadmap' );

	shipped_flag_used();

	return shipped_render_roadmap( $config['category'], $rows, $config['heading'] );
}
add_shortcode( 'shipped_roadmap', 'shipped_roadmap_shortcode' );

/**
 * Template tag: echo the changelog timeline from a theme template.
 *
 * Usage: `if ( function_exists( 'shipped_changelog' ) ) { shipped_changelog(); }`
 *
 * @param array $args Optional overrides: category, heading, schema (bool).
 * @return void
 */
function shipped_changelog( $args = array() ) {
	$config = shipped_template_args( $args, 'changelog', __( 'Changelog', 'shipped-changelog' ) );

	shipped_flag_used();

	// Output is fully escaped inside the renderer.
	echo shipped_render_changelog( $config['category'], array(), $config['heading'], $config['schema'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Template tag: echo the roadmap status board from a theme template.
 *
 * Usage: `if ( function_exists( 'shipped_roadmap' ) ) { shipped_roadmap(); }`
 *
 * @param array $args Optional overrides: category, heading.
 * @return void
 */
function shipped_roadmap( $args = array() ) {
	$config = shipped_template_args( $args, 'roadmap', __( 'Roadmap', 'shipped-changelog' ) );

	shipped_flag_used();

	// Output is fully escaped inside the renderer.
	echo shipped_render_roadmap( $config['category'], array(), $config['heading'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Normalize template-tag args (mirrors shortcode attribute handling).
 *
 * @param array  $args            Caller args.
 * @param string $mode            'changelog' or 'roadmap'.
 * @param string $default_heading Default heading.
 * @return array{category:string,heading:string,schema:bool}
 */
function shipped_template_args( $args, $mode, $default_heading ) {
	$args = wp_parse_args(
		is_array( $args ) ? $args : array(),
		array(
			'category' => 'roadmap' === $mode ? shipped_roadmap_category() : shipped_changelog_category(),
			'heading'  => $default_heading,
			'schema'   => 'changelog' === $mode,
		)
	);

	return array(
		'category' => sanitize_text_field( (string) $args['category'] ),
		'heading'  => sanitize_text_field( (string) $args['heading'] ),
		'schema'   => (bool) $args['schema'],
	);
}
