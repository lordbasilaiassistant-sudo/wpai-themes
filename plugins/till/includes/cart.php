<?php
/**
 * The cart: a cookie-backed store (no PHP sessions, so it works everywhere,
 * including WordPress Playground), the AJAX endpoints that mutate it, and the
 * slide-in drawer rendered into the footer of every page.
 *
 * @package Till
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the cart as an array of product_id => quantity.
 *
 * @return array<int,int>
 */
function till_cart_get() {
	if ( empty( $_COOKIE['till_cart'] ) ) {
		return array();
	}
	$raw  = json_decode( wp_unslash( $_COOKIE['till_cart'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded + cast below.
	$cart = array();
	if ( is_array( $raw ) ) {
		foreach ( $raw as $id => $qty ) {
			$id  = (int) $id;
			$qty = (int) $qty;
			if ( $id > 0 && $qty > 0 && 'product' === get_post_type( $id ) ) {
				$cart[ $id ] = min( $qty, 99 );
			}
		}
	}
	return $cart;
}

/**
 * Persist the cart to the cookie (browser + current request).
 *
 * @param array<int,int> $cart Cart map.
 */
function till_cart_set( $cart ) {
	$json = wp_json_encode( $cart );
	// 30-day cookie, root path. Available to the rest of this request too.
	setcookie( 'till_cart', $json, time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	$_COOKIE['till_cart'] = $json;
}

/**
 * Total number of items in the cart.
 *
 * @return int
 */
function till_cart_count() {
	return array_sum( till_cart_get() );
}

/**
 * Cart subtotal in raw float.
 *
 * @return float
 */
function till_cart_subtotal() {
	$total = 0.0;
	foreach ( till_cart_get() as $id => $qty ) {
		$total += till_get_price( $id ) * $qty;
	}
	return $total;
}

/* -------------------------------------------------------------------------
 * AJAX
 * ---------------------------------------------------------------------- */

/**
 * Shared nonce check for the cart endpoints.
 */
function till_check_nonce() {
	if ( ! check_ajax_referer( 'till', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Session expired — please refresh.', 'till' ) ), 403 );
	}
}

/**
 * Add a product to the cart.
 */
function till_ajax_add() {
	till_check_nonce();
	$id  = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	$qty = isset( $_POST['qty'] ) ? max( 1, (int) $_POST['qty'] ) : 1;

	if ( $id <= 0 || 'product' !== get_post_type( $id ) || ! till_in_stock( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'That product is unavailable.', 'till' ) ), 400 );
	}

	$cart        = till_cart_get();
	$cart[ $id ] = min( ( isset( $cart[ $id ] ) ? $cart[ $id ] : 0 ) + $qty, 99 );
	till_cart_set( $cart );

	wp_send_json_success( till_cart_payload( get_the_title( $id ) ) );
}
add_action( 'wp_ajax_till_add', 'till_ajax_add' );
add_action( 'wp_ajax_nopriv_till_add', 'till_ajax_add' );

/**
 * Update a single line's quantity (0 removes it).
 */
function till_ajax_update() {
	till_check_nonce();
	$id  = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	$qty = isset( $_POST['qty'] ) ? (int) $_POST['qty'] : 0;

	$cart = till_cart_get();
	if ( $id > 0 && isset( $cart[ $id ] ) ) {
		if ( $qty > 0 ) {
			$cart[ $id ] = min( $qty, 99 );
		} else {
			unset( $cart[ $id ] );
		}
		till_cart_set( $cart );
	}
	wp_send_json_success( till_cart_payload() );
}
add_action( 'wp_ajax_till_update', 'till_ajax_update' );
add_action( 'wp_ajax_nopriv_till_update', 'till_ajax_update' );

/**
 * The JSON payload returned by every cart mutation: fresh count, subtotal, and
 * re-rendered drawer + page-cart markup so the UI stays authoritative.
 *
 * @param string $added Optional name of the just-added product (for the toast).
 * @return array
 */
function till_cart_payload( $added = '' ) {
	return array(
		'count'       => till_cart_count(),
		'subtotal'    => till_price( till_cart_subtotal() ),
		'drawer'      => till_render_cart_lines( true ),
		'page'        => till_render_cart_lines( false ),
		'added'       => $added,
	);
}

/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/**
 * Render the cart line items, used both in the drawer and on the cart page.
 *
 * @param bool $compact Drawer (true) vs. full cart-page table (false).
 * @return string HTML.
 */
function till_render_cart_lines( $compact = true ) {
	$cart = till_cart_get();

	if ( empty( $cart ) ) {
		return '<div class="till-cart-empty">'
			. '<p>' . esc_html__( 'Your cart is empty.', 'till' ) . '</p>'
			. '<a class="till-btn" href="' . esc_url( till_page_url( 'shop' ) ) . '">' . esc_html__( 'Browse the shop', 'till' ) . '</a>'
			. '</div>';
	}

	$out = '<ul class="till-lines' . ( $compact ? ' till-lines--compact' : '' ) . '">';
	foreach ( $cart as $id => $qty ) {
		$line = till_get_price( $id ) * $qty;
		$out .= '<li class="till-line" data-till-line="' . esc_attr( $id ) . '">';
		$out .= '<a class="till-line__media" href="' . esc_url( get_permalink( $id ) ) . '"><span class="till-thumb">' . till_thumb( $id, 'thumbnail' ) . '</span></a>';
		$out .= '<div class="till-line__main">';
		$out .= '<a class="till-line__title" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>';
		$out .= '<span class="till-line__price">' . esc_html( till_price( till_get_price( $id ) ) ) . '</span>';
		$out .= '<div class="till-line__controls">';
		$out .= '<div class="till-qty till-qty--sm">';
		$out .= '<button type="button" class="till-qty__btn" data-till-line-qty="' . esc_attr( $id ) . '" data-till-delta="-1" aria-label="' . esc_attr__( 'Decrease quantity', 'till' ) . '">&minus;</button>';
		$out .= '<span class="till-qty__val">' . esc_html( $qty ) . '</span>';
		$out .= '<button type="button" class="till-qty__btn" data-till-line-qty="' . esc_attr( $id ) . '" data-till-delta="1" aria-label="' . esc_attr__( 'Increase quantity', 'till' ) . '">+</button>';
		$out .= '</div>';
		$out .= '<button type="button" class="till-line__remove" data-till-remove="' . esc_attr( $id ) . '">' . esc_html__( 'Remove', 'till' ) . '</button>';
		$out .= '</div>';
		$out .= '</div>';
		$out .= '<span class="till-line__total">' . esc_html( till_price( $line ) ) . '</span>';
		$out .= '</li>';
	}
	$out .= '</ul>';

	$out .= '<div class="till-cart-summary">';
	$out .= '<div class="till-cart-summary__row"><span>' . esc_html__( 'Subtotal', 'till' ) . '</span><strong data-till-subtotal>' . esc_html( till_price( till_cart_subtotal() ) ) . '</strong></div>';
	$out .= '<p class="till-cart-summary__note">' . esc_html__( 'Shipping & taxes calculated at checkout.', 'till' ) . '</p>';
	if ( $compact ) {
		$out .= '<a class="till-btn till-btn--block" href="' . esc_url( till_page_url( 'cart' ) ) . '">' . esc_html__( 'View cart', 'till' ) . '</a>';
		$out .= '<a class="till-btn till-btn--block till-btn--solid" href="' . esc_url( till_page_url( 'checkout' ) ) . '">' . esc_html__( 'Checkout', 'till' ) . '</a>';
	} else {
		$out .= '<a class="till-btn till-btn--block till-btn--solid till-btn--lg" href="' . esc_url( till_page_url( 'checkout' ) ) . '">' . esc_html__( 'Proceed to checkout', 'till' ) . '</a>';
		$out .= '<a class="till-cart-summary__continue" href="' . esc_url( till_page_url( 'shop' ) ) . '">' . esc_html__( 'Continue shopping', 'till' ) . '</a>';
	}
	$out .= '</div>';

	return $out;
}

/**
 * The cart toggle button markup (count badge), for themes to drop into headers.
 *
 * @param string $label Accessible label.
 * @return string HTML.
 */
function till_cart_button( $label = '' ) {
	$count = till_cart_count();
	$label = $label ? $label : __( 'Cart', 'till' );
	return '<button class="till-cart-toggle" data-till-open-cart aria-label="' . esc_attr( $label ) . '">'
		. '<svg class="till-cart-toggle__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 2h3l2.4 12.2a1.6 1.6 0 0 0 1.6 1.3h8.4a1.6 1.6 0 0 0 1.6-1.3L22 6H6"/></svg>'
		. '<span class="till-cart-toggle__count' . ( $count ? ' is-filled' : '' ) . '" data-till-count>' . esc_html( $count ) . '</span>'
		. '</button>';
}

/**
 * Inject the cart drawer + toast into the footer of every page.
 */
function till_render_drawer() {
	echo '<div class="till-drawer" id="till-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Shopping cart', 'till' ) . '">';
	echo '<div class="till-drawer__scrim" data-till-close-cart></div>';
	echo '<aside class="till-drawer__panel">';
	echo '<header class="till-drawer__head"><h2>' . esc_html__( 'Your cart', 'till' ) . '</h2>';
	echo '<button class="till-drawer__close" data-till-close-cart aria-label="' . esc_attr__( 'Close cart', 'till' ) . '">&times;</button></header>';
	echo '<div class="till-drawer__body" data-till-cart-drawer>';
	echo till_render_cart_lines( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled, escaped above.
	echo '</div>';
	echo '</aside>';
	echo '</div>';

	echo '<div class="till-toast" data-till-toast aria-live="polite" hidden></div>';
}
add_action( 'wp_footer', 'till_render_drawer' );
