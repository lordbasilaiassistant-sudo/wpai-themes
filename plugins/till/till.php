<?php
/**
 * Plugin Name:       Till — Commerce
 * Plugin URI:        https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description:        A complete, self-contained store for WordPress — products, a slide-in cart, and a clean checkout. No external services, no account, no monthly fee. Pairs with the Emporium theme to build a Shopify-grade storefront in minutes.
 * Version:           1.0.0
 * Author:            WPAI Themes
 * Author URI:        https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       till
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Category:          E-commerce
 *
 * @package Till
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TILL_VERSION', '1.0.0' );
define( 'TILL_FILE', __FILE__ );
define( 'TILL_DIR', plugin_dir_path( __FILE__ ) );
define( 'TILL_URL', plugin_dir_url( __FILE__ ) );

require_once TILL_DIR . 'includes/products.php';
require_once TILL_DIR . 'includes/cart.php';
require_once TILL_DIR . 'includes/shortcodes.php';
require_once TILL_DIR . 'includes/seed.php';

/**
 * Front-end styles and scripts. Loaded everywhere because the cart drawer and
 * its live count badge live in the footer on every page.
 */
function till_assets() {
	wp_enqueue_style( 'till', TILL_URL . 'assets/till.css', array(), TILL_VERSION );

	wp_enqueue_script( 'till', TILL_URL . 'assets/till.js', array(), TILL_VERSION, true );
	wp_localize_script(
		'till',
		'TILL',
		array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'till' ),
			'cartUrl'  => till_page_url( 'cart' ),
			'currency' => till_currency(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'till_assets' );

/**
 * Activation: register types, build the store pages, seed a demo catalog, and
 * flush rewrite rules so /shop and single products resolve immediately.
 */
function till_activate() {
	till_register_product_type();
	till_register_taxonomies();
	till_install_pages();
	till_seed_demo_store();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'till_activate' );

/**
 * Deactivation: tidy rewrite rules.
 */
function till_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'till_deactivate' );

/**
 * The store currency symbol, filterable.
 *
 * @return string
 */
function till_currency() {
	return apply_filters( 'till_currency', '$' );
}

/**
 * Format a numeric amount as a price string (e.g. 24 -> "$24.00").
 *
 * @param float|string $amount Raw amount.
 * @return string
 */
function till_price( $amount ) {
	$amount = (float) $amount;
	return till_currency() . number_format_i18n( $amount, 2 );
}

/**
 * Resolve the URL of a Till store page by its option key (shop|cart|checkout).
 *
 * @param string $key Page key.
 * @return string
 */
function till_page_url( $key ) {
	$id = (int) get_option( 'till_page_' . $key );
	if ( $id && get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	return home_url( '/' );
}
