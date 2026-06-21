<?php
/**
 * Plugin Name: Shipped — Auto Changelog & Roadmap
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: A self-building changelog timeline and roadmap status board — automated product management with zero config. Drop [shipped_changelog] and [shipped_roadmap] anywhere; entries build themselves from your "Changelog" and "Roadmap" post categories. Theme-adaptive, accessible, cached.
 * Category:   Automation
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shipped-changelog
 *
 * @package Shipped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const SHIPPED_VERSION = '1.0.0';

/**
 * Absolute path to this plugin's main file. Used for asset URLs.
 */
define( 'SHIPPED_FILE', __FILE__ );

/**
 * Transient lifetime for the changelog / roadmap query caches (12 hours).
 *
 * The caches are also invalidated immediately on save_post / deleted_post (see
 * shipped_clear_cache), so the TTL is just a long backstop, never the primary
 * freshness mechanism.
 */
const SHIPPED_CACHE_TTL = 12 * HOUR_IN_SECONDS;

// ---------------------------------------------------------------------------
// Bootstrap: load the focused modules. Each file guards direct access itself.
//   - data.php       : category/term resolution + cached, tuned WP_Query helpers
//   - render.php      : escaped HTML builders for the timeline and status board
//   - shortcodes.php  : [shipped_changelog] / [shipped_roadmap] + template tags
//   - assets.php      : conditional CSS/JS enqueue, defer, no-js class swap
// ---------------------------------------------------------------------------
require_once plugin_dir_path( __FILE__ ) . 'includes/data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/render.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/assets.php';

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function shipped_load_textdomain() {
	load_plugin_textdomain( 'shipped-changelog', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'shipped_load_textdomain' );

/**
 * Invalidate the cached changelog / roadmap data when content changes.
 *
 * Only the 'post' post type is ever queried (see shipped_get_entry_ids), so we
 * skip any other type — saving/deleting pages, menu items, attachments, etc.
 * never touches the timeline/board and shouldn't pay for a cache flush. We can't
 * cheaply know whether the saved post belonged to a watched category (its terms
 * may have just changed), so for the 'post' type we simply clear both caches —
 * they are inexpensive to rebuild and this keeps the display correct the instant
 * an entry is published or edited.
 *
 * @param int          $post_id The post being saved or deleted.
 * @param WP_Post|null $post    The post object, when available (save_post path).
 * @return void
 */
function shipped_clear_cache( $post_id, $post = null ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// deleted_post does not always pass the object on older cores; fetch it.
	if ( ! $post instanceof WP_Post ) {
		$post = get_post( $post_id );
	}

	if ( $post instanceof WP_Post && 'post' !== $post->post_type ) {
		return;
	}

	shipped_flush_caches();
}
add_action( 'save_post', 'shipped_clear_cache', 10, 2 );
add_action( 'deleted_post', 'shipped_clear_cache', 10, 2 );

// Term edits (e.g. renaming/retagging) can also change what an entry shows.
add_action( 'edited_term', 'shipped_flush_caches' );
add_action( 'set_object_terms', 'shipped_flush_caches' );
