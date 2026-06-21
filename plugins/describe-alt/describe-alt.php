<?php
/**
 * Plugin Name: Describe — Auto Alt Text
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Automatically gives images meaningful alt text so your site is accessible and SEO-friendly — with zero manual work and no external AI. On upload it fills empty alt from the title, caption, or a humanized filename; on the front end it backfills any image still missing alt. Never overwrites alt a human wrote. Works on any theme.
 * Category:   Automation
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: describe-alt
 *
 * @package DescribeAlt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const DESCRIBE_ALT_VERSION = '1.0.0';

/**
 * Absolute path to this plugin's main file. Used for activation hooks and URLs.
 */
define( 'DESCRIBE_ALT_FILE', __FILE__ );

// ---------------------------------------------------------------------------
// Bootstrap: load the focused modules. Each file guards direct access itself.
//
//   text.php      — pure, network-free helpers that DERIVE alt text locally
//                   (filename humanization, title/caption/filename resolution).
//   uploader.php  — Layer 1: set _wp_attachment_image_alt on upload when empty.
//   frontend.php  — Layer 2: backfill still-empty alt on the front end.
//   admin.php     — optional, zero-config status panel (Media > Auto Alt Text).
// ---------------------------------------------------------------------------
require_once plugin_dir_path( __FILE__ ) . 'includes/text.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/uploader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/frontend.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function describe_alt_load_textdomain() {
	load_plugin_textdomain( 'describe-alt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'describe_alt_load_textdomain' );

/**
 * Deactivation: drop the cached coverage stat so a re-activation recomputes it.
 *
 * Nothing else to clean up — the plugin stores no options and writes alt text
 * only into WordPress's own _wp_attachment_image_alt meta, which legitimately
 * persists (the images keep their accessible descriptions after deactivation).
 *
 * @return void
 */
function describe_alt_deactivate() {
	delete_transient( 'describe_alt_coverage' );
}
register_deactivation_hook( __FILE__, 'describe_alt_deactivate' );
