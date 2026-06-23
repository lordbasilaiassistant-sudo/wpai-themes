<?php
/**
 * Plugin Name:       Keepsake — Wishlist
 * Plugin URI:        https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description:        A tasteful, instant wishlist for your store. A heart on every product, a saved-items page, and a count in the header — all client-side, no account required. Pairs with Till — Commerce.
 * Version:           1.0.0
 * Author:            WPAI Themes
 * Author URI:        https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       keepsake
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Category:          E-commerce
 *
 * @package Keepsake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KEEPSAKE_VERSION', '1.0.0' );
define( 'KEEPSAKE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Front-end assets.
 */
function keepsake_assets() {
	wp_enqueue_style( 'keepsake', KEEPSAKE_URL . 'assets/keepsake.css', array(), KEEPSAKE_VERSION );
	wp_enqueue_script( 'keepsake', KEEPSAKE_URL . 'assets/keepsake.js', array(), KEEPSAKE_VERSION, true );
	wp_localize_script(
		'keepsake',
		'KEEPSAKE',
		array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'keepsake' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'keepsake_assets' );

/**
 * The heart toggle button markup.
 *
 * @param int  $id    Product ID.
 * @param bool $label Show a text label beside the heart.
 * @return string
 */
function keepsake_heart( $id, $label = false ) {
	$svg = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M12 21s-7.5-4.6-10-9.2C.3 8.3 1.7 4.7 5.1 4.1c2-.3 3.8.8 4.9 2.4 1.1-1.6 2.9-2.7 4.9-2.4 3.4.6 4.8 4.2 3.1 7.7C19.5 16.4 12 21 12 21z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
	$text = $label ? '<span class="keepsake-heart__label">' . esc_html__( 'Save', 'keepsake' ) . '</span>' : '';
	return '<button class="keepsake-heart' . ( $label ? ' keepsake-heart--inline' : '' ) . '" type="button" data-keepsake="' . esc_attr( $id ) . '" aria-pressed="false" aria-label="' . esc_attr__( 'Save to wishlist', 'keepsake' ) . '">' . $svg . $text . '</button>';
}

/**
 * Add a heart to the top-right corner of every Till product card.
 *
 * @param string $html Existing corner markup.
 * @param int    $id   Product ID.
 * @return string
 */
function keepsake_card_corner( $html, $id ) {
	return $html . keepsake_heart( $id );
}
add_filter( 'till_card_corner', 'keepsake_card_corner', 10, 2 );

/**
 * Add a "Save" button beside add-to-cart on single products.
 *
 * @param string $html Existing markup.
 * @param int    $id   Product ID.
 * @return string
 */
function keepsake_single_buy( $html, $id ) {
	return $html . keepsake_heart( $id, true );
}
add_filter( 'till_single_buy', 'keepsake_single_buy', 10, 2 );

/**
 * A header wishlist link with a live saved-count badge, for themes to use.
 *
 * @return string
 */
function keepsake_count_link() {
	$page = (int) get_option( 'keepsake_page' );
	$url  = $page ? get_permalink( $page ) : home_url( '/wishlist/' );
	return '<a class="keepsake-link" href="' . esc_url( $url ) . '" aria-label="' . esc_attr__( 'Wishlist', 'keepsake' ) . '">'
		. '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M12 21s-7.5-4.6-10-9.2C.3 8.3 1.7 4.7 5.1 4.1c2-.3 3.8.8 4.9 2.4 1.1-1.6 2.9-2.7 4.9-2.4 3.4.6 4.8 4.2 3.1 7.7C19.5 16.4 12 21 12 21z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>'
		. '<span class="keepsake-link__count" data-keepsake-count hidden>0</span></a>';
}

/**
 * [keepsake_list] — the saved-items page. JS fills it from localStorage.
 *
 * @return string
 */
function keepsake_list_shortcode() {
	return '<div class="keepsake-list" data-keepsake-list>'
		. '<div class="keepsake-list__empty" data-keepsake-empty>'
		. '<p>' . esc_html__( 'Your wishlist is empty.', 'keepsake' ) . '</p>'
		. '<p class="keepsake-list__hint">' . esc_html__( 'Tap the heart on any product to save it here for later.', 'keepsake' ) . '</p>'
		. '</div>'
		. '<div class="keepsake-list__grid" data-keepsake-grid></div>'
		. '</div>';
}
add_shortcode( 'keepsake_list', 'keepsake_list_shortcode' );

/**
 * AJAX: render Till product cards for a set of saved IDs.
 */
function keepsake_render_cards() {
	if ( ! check_ajax_referer( 'keepsake', 'nonce', false ) ) {
		wp_send_json_error( array(), 403 );
	}
	if ( ! function_exists( 'till_product_card' ) ) {
		wp_send_json_error( array( 'message' => __( 'Till — Commerce is required.', 'keepsake' ) ), 400 );
	}

	$ids = isset( $_POST['ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['ids'] ) ) : array();
	$ids = array_filter(
		$ids,
		function ( $id ) {
			return $id > 0 && 'product' === get_post_type( $id ) && 'publish' === get_post_status( $id );
		}
	);

	$html = '';
	foreach ( $ids as $id ) {
		$html .= till_product_card( $id );
	}
	wp_send_json_success( array( 'html' => $html, 'ids' => array_values( $ids ) ) );
}
add_action( 'wp_ajax_keepsake_cards', 'keepsake_render_cards' );
add_action( 'wp_ajax_nopriv_keepsake_cards', 'keepsake_render_cards' );

/**
 * On activation, create the Wishlist page.
 */
function keepsake_activate() {
	$existing = (int) get_option( 'keepsake_page' );
	if ( $existing && 'page' === get_post_type( $existing ) ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Wishlist', 'keepsake' ),
			'post_name'    => 'wishlist',
			'post_content' => '[keepsake_list]',
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'keepsake_page', $id );
	}
}
register_activation_hook( __FILE__, 'keepsake_activate' );
