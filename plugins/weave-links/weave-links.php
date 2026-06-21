<?php
/**
 * Plugin Name: Weave — Auto Internal Links
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Automatically weaves a web of internal links with zero effort. On single posts it hyperlinks the first mention of your other published posts' titles to those posts — whole-word, case-insensitive, never inside existing links, headings, or code. Theme-adaptive, accessible, cached, zero configuration.
 * Category:   Automation
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: weave-links
 *
 * @package Weave
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const WEAVE_VERSION = '1.0.0';

/**
 * Default maximum number of auto-links injected into a single post.
 *
 * Tunable via the `weave_max_links` filter. Kept low on purpose: a handful of
 * relevant internal links reads as editorial, a wall of them reads as spam.
 */
const WEAVE_MAX_LINKS = 5;

/**
 * Default minimum length (in characters) of a post title before it is eligible
 * to be matched. Short titles ("News", "Blog", "Home") match too aggressively,
 * so they are skipped by default. Tunable via `weave_min_title_length`.
 */
const WEAVE_MIN_TITLE_LENGTH = 4;

/**
 * Transient key for the cached title dictionary (title => post ID map).
 *
 * The version is folded in so a plugin update invalidates any old structure.
 */
const WEAVE_DICTIONARY_KEY = 'weave_dictionary_' . WEAVE_VERSION;

/**
 * Transient lifetime for the dictionary cache (12 hours).
 *
 * The dictionary is also rebuilt eagerly whenever a post is saved or deleted
 * (see weave_clear_dictionary), so the TTL is just a backstop for sites whose
 * content changes outside the normal save flow.
 */
const WEAVE_DICTIONARY_TTL = 12 * HOUR_IN_SECONDS;

// ---------------------------------------------------------------------------
// Bootstrap: load the focused modules. Each file guards direct access itself.
// ---------------------------------------------------------------------------
require_once plugin_dir_path( __FILE__ ) . 'includes/dictionary.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/linker.php';

/**
 * Whether Weave should weave links into the current request.
 *
 * Active only on the front end, in the main loop, for a single post — never in
 * the admin, feeds, REST/AJAX, embeds, or secondary queries. Centralized so the
 * content filter and the asset enqueue can never drift apart, and filterable so
 * a site can opt specific views in or out.
 *
 * @return bool
 */
function weave_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		&& is_singular( 'post' );

	/**
	 * Filter whether Weave is active for this request.
	 *
	 * Returning false disables both the in-content link weaving and the asset
	 * enqueue for the current view.
	 *
	 * @param bool $active Whether Weave should run on this request.
	 */
	return (bool) apply_filters( 'weave_is_active', $active );
}

/**
 * Weave internal links into single-post content.
 *
 * Guards on the main query in the loop for single posts only, so links are never
 * injected into excerpts, archives, feeds, REST responses, or secondary queries.
 * The heavy lifting (safe parsing, protected-region skipping, escaping) lives in
 * weave_link_content(); this is just the placement guard.
 *
 * Hooked at priority 12 — after WordPress core formatting (wpautop at 10) so we
 * operate on finished HTML, but before late filters like Kindred's related-posts
 * append (25), so we never scan injected widget markup.
 *
 * @param string $content The post content.
 * @return string
 */
function weave_filter_content( $content ) {
	if ( ! weave_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = (int) get_the_ID();
	if ( ! $post_id ) {
		return $content;
	}

	// Output is escaped at the point each anchor is built (see weave_build_anchor).
	return weave_link_content( $content, $post_id );
}
add_filter( 'the_content', 'weave_filter_content', 12 );

/**
 * Register and enqueue the auto-link stylesheet.
 *
 * Ships as a real, versioned asset file (assets/weave-links.css) rather than an
 * inline blob, and loads only on single posts where links can appear. The
 * styling is purely cosmetic — links work and are fully accessible without it.
 *
 * @return void
 */
function weave_enqueue_assets() {
	if ( ! weave_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'weave-links',
		plugins_url( 'assets/weave-links.css', __FILE__ ),
		array(),
		WEAVE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'weave_enqueue_assets' );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function weave_load_textdomain() {
	load_plugin_textdomain( 'weave-links', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'weave_load_textdomain' );

/**
 * Drop the cached dictionary on activation and deactivation.
 *
 * Activation clears any stale data so the first request rebuilds fresh;
 * deactivation cleans up after the plugin so nothing lingers in the options
 * table.
 *
 * @return void
 */
function weave_flush_dictionary() {
	delete_transient( WEAVE_DICTIONARY_KEY );
}
register_activation_hook( __FILE__, 'weave_flush_dictionary' );
register_deactivation_hook( __FILE__, 'weave_flush_dictionary' );
