<?php
/**
 * Plugin Name: Beacon — AI & SEO
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Makes your site discoverable by search engines AND AI agents, zero config. Auto meta tags, Open Graph, Twitter cards, JSON-LD structured data, and a machine-readable /llms.txt for LLMs. Works on any theme.
 * Category:   SEO & AI
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: beacon-ai-seo
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const BEACON_VERSION = '1.0.0';

/**
 * Absolute path to this plugin's main file. Used for activation hooks and URLs.
 */
define( 'BEACON_FILE', __FILE__ );

// ---------------------------------------------------------------------------
// Bootstrap: load the focused modules. Each file guards direct access itself.
// ---------------------------------------------------------------------------
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/meta-tags.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/structured-data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/llms-txt.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function beacon_load_textdomain() {
	load_plugin_textdomain( 'beacon-ai-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'beacon_load_textdomain' );

/**
 * Activation: register the /llms.txt rewrite rule, then flush so it takes effect.
 *
 * @return void
 */
function beacon_activate() {
	beacon_register_llms_rewrite();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'beacon_activate' );

/**
 * Deactivation: flush rewrite rules so the /llms.txt route is removed cleanly.
 *
 * The rule itself is only added on the `init` hook (which does not run during
 * deactivation), so a plain flush drops it.
 *
 * @return void
 */
function beacon_deactivate() {
	delete_transient( 'beacon_llms_txt' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'beacon_deactivate' );
