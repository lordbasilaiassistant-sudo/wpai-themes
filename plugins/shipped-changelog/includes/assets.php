<?php
/**
 * Asset layer for Shipped — Auto Changelog & Roadmap.
 *
 * Registers the stylesheet and reveal script as real, versioned files and loads
 * them ONLY where a [shipped_changelog] / [shipped_roadmap] block actually
 * appears. Two paths cover every placement:
 *
 *   1. In post content: we sniff the singular post's content for either
 *      shortcode at `wp_enqueue_scripts` and enqueue in the <head> — no flash.
 *   2. In a template (do_shortcode / template tag) or a widget/block we can't
 *      pre-detect: shipped_flag_used() enqueues on demand at render time; if
 *      that lands after the head, WordPress prints the late style in the footer,
 *      and the no-JS reveal default keeps everything visible regardless.
 *
 * The reveal script is deferred and pure progressive enhancement.
 *
 * @package Shipped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Register (but do not yet enqueue) the plugin's style and script.
 *
 * Registering on wp_enqueue_scripts means both handles exist for any later
 * on-demand enqueue, even one triggered mid-render by a shortcode.
 *
 * @return void
 */
function shipped_register_assets() {
	wp_register_style(
		'shipped-changelog',
		plugins_url( 'assets/shipped-changelog.css', SHIPPED_FILE ),
		array(),
		SHIPPED_VERSION
	);

	wp_register_script(
		'shipped-changelog-motion',
		plugins_url( 'assets/js/motion.js', SHIPPED_FILE ),
		array(),
		SHIPPED_VERSION,
		true // Footer.
	);
}
add_action( 'wp_enqueue_scripts', 'shipped_register_assets', 5 );

/**
 * Enqueue the registered assets immediately.
 *
 * Idempotent: wp_enqueue_* de-dupes by handle, so the content sniff and the
 * on-demand render path can both call this safely.
 *
 * @return void
 */
function shipped_enqueue_assets_now() {
	// Make sure registration has happened (e.g. when called very early).
	if ( ! wp_style_is( 'shipped-changelog', 'registered' ) ) {
		shipped_register_assets();
	}

	wp_enqueue_style( 'shipped-changelog' );
	wp_enqueue_script( 'shipped-changelog-motion' );
}

/**
 * On singular views, enqueue in the head when the content contains a shortcode.
 *
 * This is the zero-flash fast path: the CSS is in the <head> before the body
 * renders. Bails cheaply on every page that doesn't use the shortcodes.
 *
 * @return void
 */
function shipped_maybe_enqueue_for_content() {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if (
		has_shortcode( $post->post_content, 'shipped_changelog' ) ||
		has_shortcode( $post->post_content, 'shipped_roadmap' )
	) {
		shipped_enqueue_assets_now();
	}
}
add_action( 'wp_enqueue_scripts', 'shipped_maybe_enqueue_for_content', 10 );

/**
 * Add a `defer` attribute to the reveal script tag.
 *
 * Keeps the enhancement non-render-blocking and supports WordPress back to 5.0
 * (the `strategy` enqueue arg only arrived in 6.3). A no-op for every other
 * handle.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function shipped_defer_script( $tag, $handle ) {
	if ( 'shipped-changelog-motion' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'shipped_defer_script', 10, 2 );

/**
 * Print the early "JS available" class swap in the <head>.
 *
 * Swaps `shipped-no-js` on <html> for `shipped-js` the instant scripting is
 * confirmed. The reveal CSS is scoped to `.shipped-js`, so a no-JS visitor — or
 * one whose script fails — always sees entries fully rendered (true progressive
 * enhancement, zero flash). Printed only on singular views with the shortcode
 * in content so it never runs site-wide. The snippet is a fixed literal.
 *
 * @return void
 */
function shipped_print_js_class() {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	$post = get_post();
	if (
		! $post instanceof WP_Post ||
		(
			! has_shortcode( $post->post_content, 'shipped_changelog' ) &&
			! has_shortcode( $post->post_content, 'shipped_roadmap' )
		)
	) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('shipped-no-js');document.documentElement.classList.add('shipped-js');</script>\n";
}
add_action( 'wp_head', 'shipped_print_js_class', 1 );

/**
 * Seed the <html> element with a `shipped-no-js` class on relevant views.
 *
 * The reveal CSS is scoped to `.shipped-js`, so this default guarantees no-JS
 * visitors keep entries visible; the head snippet promotes it to `shipped-js`
 * when scripting is confirmed.
 *
 * The `language_attributes` filter passes the FULL attribute string for <html>
 * (e.g. `lang="en-US"`), not a bare class list, so we merge our class into an
 * existing `class="..."` attribute when present and append a new one otherwise.
 *
 * @param string $output Full attribute string for the <html> element.
 * @return string
 */
function shipped_html_no_js_class( $output ) {
	if ( is_admin() || ! is_singular() ) {
		return $output;
	}

	$post = get_post();
	if (
		! $post instanceof WP_Post ||
		(
			! has_shortcode( $post->post_content, 'shipped_changelog' ) &&
			! has_shortcode( $post->post_content, 'shipped_roadmap' )
		)
	) {
		return $output;
	}

	if ( false !== strpos( $output, 'shipped-no-js' ) ) {
		return $output;
	}

	$merged = preg_replace(
		'/\bclass=("|\')(.*?)\1/',
		'class=$1$2 shipped-no-js$1',
		$output,
		1,
		$replaced
	);

	if ( $replaced ) {
		return $merged;
	}

	$output = trim( $output );

	return '' === $output ? 'class="shipped-no-js"' : $output . ' class="shipped-no-js"';
}
add_filter( 'language_attributes', 'shipped_html_no_js_class' );
