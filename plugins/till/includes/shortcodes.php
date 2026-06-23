<?php
/**
 * The store shortcodes: [till_shop], [till_featured], [till_cart] and
 * [till_checkout]. Till's install routine drops the latter three onto pages.
 *
 * @package Till
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [till_shop] — a filterable product grid with category links and a sort menu.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function till_sc_shop( $atts ) {
	$atts = shortcode_atts(
		array(
			'per_page' => 12,
			'columns'  => 4,
		),
		$atts,
		'till_shop'
	);

	$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$active   = isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';
	$sort     = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : '';

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => (int) $atts['per_page'],
		'paged'          => $paged,
	);

	switch ( $sort ) {
		case 'price-asc':
			$args['meta_key'] = '_till_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'price-desc':
			$args['meta_key'] = '_till_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'title':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
	}

	if ( $active ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $active,
			),
		);
	}

	$q = new WP_Query( $args );

	// Category filter bar.
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	$base  = get_permalink();
	$bar   = '<div class="till-shopbar"><nav class="till-filters" aria-label="' . esc_attr__( 'Product categories', 'till' ) . '">';
	$bar  .= '<a class="till-filter' . ( '' === $active ? ' is-active' : '' ) . '" href="' . esc_url( $base ) . '">' . esc_html__( 'All', 'till' ) . '</a>';
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$url  = add_query_arg( 'cat', $term->slug, $base );
			$bar .= '<a class="till-filter' . ( $active === $term->slug ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
		}
	}
	$bar .= '</nav>';

	// Sort menu (plain GET form so it works without JS).
	$bar .= '<form class="till-sort" method="get" action="' . esc_url( $base ) . '">';
	if ( $active ) {
		$bar .= '<input type="hidden" name="cat" value="' . esc_attr( $active ) . '" />';
	}
	$bar .= '<label class="till-sort__label" for="till-sort">' . esc_html__( 'Sort', 'till' ) . '</label>';
	$bar .= '<select id="till-sort" name="sort" onchange="this.form.submit()">';
	$options = array(
		''           => __( 'Newest', 'till' ),
		'price-asc'  => __( 'Price: low to high', 'till' ),
		'price-desc' => __( 'Price: high to low', 'till' ),
		'title'      => __( 'Alphabetical', 'till' ),
	);
	foreach ( $options as $val => $label ) {
		$bar .= '<option value="' . esc_attr( $val ) . '"' . selected( $sort, $val, false ) . '>' . esc_html( $label ) . '</option>';
	}
	$bar .= '</select></form></div>';

	if ( ! $q->have_posts() ) {
		return $bar . '<p class="till-empty">' . esc_html__( 'No products match that filter yet.', 'till' ) . '</p>';
	}

	$ids = wp_list_pluck( $q->posts, 'ID' );
	$out = $bar . '<div class="till-grid" style="--till-cols:' . (int) $atts['columns'] . '">';
	foreach ( $ids as $id ) {
		$out .= till_product_card( $id );
	}
	$out .= '</div>';

	// Pagination.
	$links = paginate_links(
		array(
			'total'     => $q->max_num_pages,
			'current'   => $paged,
			'mid_size'  => 1,
			'prev_text' => __( '&larr; Prev', 'till' ),
			'next_text' => __( 'Next &rarr;', 'till' ),
			'type'      => 'array',
		)
	);
	if ( $links ) {
		$out .= '<nav class="till-pagination">' . implode( '', $links ) . '</nav>';
	}

	wp_reset_postdata();
	return $out;
}
add_shortcode( 'till_shop', 'till_sc_shop' );

/**
 * [till_featured count="4" cat=""] — a tidy row of products for landing pages.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function till_sc_featured( $atts ) {
	$atts = shortcode_atts(
		array(
			'count'   => 4,
			'cat'     => '',
			'orderby' => 'date',
		),
		$atts,
		'till_featured'
	);

	$args = array(
		'post_type'           => 'product',
		'posts_per_page'      => (int) $atts['count'],
		'orderby'             => 'rand' === $atts['orderby'] ? 'rand' : 'date',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $atts['cat'] ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $atts['cat'] ) ),
			),
		);
	}

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		return '';
	}
	$ids = wp_list_pluck( $q->posts, 'ID' );
	wp_reset_postdata();
	return till_product_grid( $ids );
}
add_shortcode( 'till_featured', 'till_sc_featured' );

/**
 * [till_cart] — the full cart page.
 *
 * @return string
 */
function till_sc_cart() {
	return '<div class="till-cart-page" data-till-cart-page>' . till_render_cart_lines( false ) . '</div>';
}
add_shortcode( 'till_cart', 'till_sc_cart' );

/**
 * [till_checkout] — a demo checkout: a real summary and a working form that
 * records an order and clears the cart. No payment is taken (it is a demo
 * store), and the confirmation screen says so plainly.
 *
 * @return string
 */
function till_sc_checkout() {
	// Order just placed?
	if ( isset( $_GET['till_order'] ) ) {
		$order_id = (int) $_GET['till_order'];
		$order    = get_post( $order_id );
		if ( $order && 'shop_order' === $order->post_type ) {
			$total = get_post_meta( $order_id, '_till_total', true );
			$name  = get_post_meta( $order_id, '_till_name', true );
			return '<div class="till-confirm">'
				. '<div class="till-confirm__check" aria-hidden="true">&#10003;</div>'
				. '<h2>' . esc_html__( 'Thank you for your order!', 'till' ) . '</h2>'
				. '<p>' . esc_html( sprintf( __( 'Order #%1$s for %2$s is confirmed.', 'till' ), $order_id, till_price( $total ) ) ) . '</p>'
				. ( $name ? '<p>' . esc_html( sprintf( __( 'A receipt is on its way, %s.', 'till' ), $name ) ) . '</p>' : '' )
				. '<p class="till-confirm__note">' . esc_html__( 'This is a demo store — no payment was taken and nothing will ship.', 'till' ) . '</p>'
				. '<a class="till-btn till-btn--solid" href="' . esc_url( till_page_url( 'shop' ) ) . '">' . esc_html__( 'Continue shopping', 'till' ) . '</a>'
				. '</div>';
		}
	}

	$cart = till_cart_get();
	if ( empty( $cart ) ) {
		return '<div class="till-cart-empty till-cart-empty--page"><p>' . esc_html__( 'Your cart is empty.', 'till' ) . '</p>'
			. '<a class="till-btn till-btn--solid" href="' . esc_url( till_page_url( 'shop' ) ) . '">' . esc_html__( 'Browse the shop', 'till' ) . '</a></div>';
	}

	$action = esc_url( admin_url( 'admin-post.php' ) );
	$out    = '<form class="till-checkout" method="post" action="' . $action . '">';
	$out   .= wp_nonce_field( 'till_place_order', 'till_order_nonce', true, false );
	$out   .= '<input type="hidden" name="action" value="till_place_order" />';

	// Customer fields (demo — labelled as such, no real processing).
	$out .= '<div class="till-checkout__form">';
	$out .= '<h2>' . esc_html__( 'Contact & shipping', 'till' ) . '</h2>';
	$out .= '<div class="till-field"><label for="till-name">' . esc_html__( 'Full name', 'till' ) . '</label><input id="till-name" type="text" name="name" required autocomplete="name" /></div>';
	$out .= '<div class="till-field"><label for="till-email">' . esc_html__( 'Email', 'till' ) . '</label><input id="till-email" type="email" name="email" required autocomplete="email" /></div>';
	$out .= '<div class="till-field"><label for="till-address">' . esc_html__( 'Address', 'till' ) . '</label><input id="till-address" type="text" name="address" autocomplete="street-address" /></div>';
	$out .= '<div class="till-field-row">';
	$out .= '<div class="till-field"><label for="till-city">' . esc_html__( 'City', 'till' ) . '</label><input id="till-city" type="text" name="city" autocomplete="address-level2" /></div>';
	$out .= '<div class="till-field"><label for="till-zip">' . esc_html__( 'Postal code', 'till' ) . '</label><input id="till-zip" type="text" name="zip" autocomplete="postal-code" /></div>';
	$out .= '</div>';
	$out .= '<h2>' . esc_html__( 'Payment', 'till' ) . '</h2>';
	$out .= '<p class="till-checkout__demo">' . esc_html__( 'Demo checkout — no card is charged. Place the order to see the confirmation.', 'till' ) . '</p>';
	$out .= '</div>';

	// Order summary.
	$out .= '<aside class="till-checkout__summary">';
	$out .= '<h2>' . esc_html__( 'Order summary', 'till' ) . '</h2>';
	$out .= '<ul class="till-summary-lines">';
	foreach ( $cart as $id => $qty ) {
		$out .= '<li><span class="till-summary-lines__name">' . esc_html( get_the_title( $id ) ) . ' <em>&times;' . esc_html( $qty ) . '</em></span>'
			. '<span>' . esc_html( till_price( till_get_price( $id ) * $qty ) ) . '</span></li>';
	}
	$out .= '</ul>';
	$subtotal = till_cart_subtotal();
	$out .= '<div class="till-summary-total"><span>' . esc_html__( 'Subtotal', 'till' ) . '</span><span>' . esc_html( till_price( $subtotal ) ) . '</span></div>';
	$out .= '<div class="till-summary-total"><span>' . esc_html__( 'Shipping', 'till' ) . '</span><span>' . esc_html__( 'Free', 'till' ) . '</span></div>';
	$out .= '<div class="till-summary-total till-summary-total--grand"><span>' . esc_html__( 'Total', 'till' ) . '</span><span>' . esc_html( till_price( $subtotal ) ) . '</span></div>';
	$out .= '<button class="till-btn till-btn--solid till-btn--block till-btn--lg" type="submit">' . esc_html__( 'Place order', 'till' ) . '</button>';
	$out .= '<p class="till-checkout__secure">' . esc_html__( '🔒 This is a safe demo. Your details are not stored or sent anywhere.', 'till' ) . '</p>';
	$out .= '</aside>';

	$out .= '</form>';
	return $out;
}
add_shortcode( 'till_checkout', 'till_sc_checkout' );

/**
 * Handle the demo order: record a private shop_order, clear the cart, redirect
 * back to the checkout page with a confirmation token.
 */
function till_place_order() {
	if ( ! isset( $_POST['till_order_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['till_order_nonce'] ), 'till_place_order' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'till' ) );
	}

	$cart = till_cart_get();
	if ( empty( $cart ) ) {
		wp_safe_redirect( till_page_url( 'shop' ) );
		exit;
	}

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$total = till_cart_subtotal();

	$lines = array();
	foreach ( $cart as $id => $qty ) {
		$lines[] = sprintf( '%s × %d — %s', get_the_title( $id ), $qty, till_price( till_get_price( $id ) * $qty ) );
	}

	$order_id = wp_insert_post(
		array(
			'post_type'    => 'shop_order',
			'post_status'  => 'private',
			'post_title'   => sprintf( __( 'Order — %s', 'till' ), $name ? $name : __( 'Guest', 'till' ) ),
			'post_content' => implode( "\n", $lines ),
		)
	);

	if ( $order_id && ! is_wp_error( $order_id ) ) {
		update_post_meta( $order_id, '_till_total', $total );
		update_post_meta( $order_id, '_till_name', $name );
		update_post_meta( $order_id, '_till_email', $email );
		update_post_meta( $order_id, '_till_items', wp_json_encode( $cart ) );
	}

	// Empty the cart.
	till_cart_set( array() );

	$redirect = add_query_arg( 'till_order', (int) $order_id, till_page_url( 'checkout' ) );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_till_place_order', 'till_place_order' );
add_action( 'admin_post_nopriv_till_place_order', 'till_place_order' );

/**
 * Register a private order post type to store demo orders for the shop admin.
 */
function till_register_orders() {
	register_post_type(
		'shop_order',
		array(
			'labels'          => array(
				'name'          => __( 'Orders', 'till' ),
				'singular_name' => __( 'Order', 'till' ),
				'menu_name'     => __( 'Orders', 'till' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=product',
			'capability_type' => 'post',
			'supports'        => array( 'title', 'editor' ),
		)
	);
}
add_action( 'init', 'till_register_orders' );
