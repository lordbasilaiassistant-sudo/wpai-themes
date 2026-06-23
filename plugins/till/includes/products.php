<?php
/**
 * The product post type, its categories, price meta, and the markup helpers the
 * store front end reuses (cards, badges, single-product layout).
 *
 * @package Till
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the `product` post type.
 */
function till_register_product_type() {
	register_post_type(
		'product',
		array(
			'labels'       => array(
				'name'          => __( 'Products', 'till' ),
				'singular_name' => __( 'Product', 'till' ),
				'add_new_item'  => __( 'Add New Product', 'till' ),
				'edit_item'     => __( 'Edit Product', 'till' ),
				'menu_name'     => __( 'Products', 'till' ),
			),
			'public'       => true,
			// A dedicated Shop page (with the [till_shop] shortcode) owns /shop/,
			// so the post-type archive lives at /product/ to avoid a collision.
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-cart',
			'menu_position' => 25,
			'rewrite'      => array( 'slug' => 'product' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'till_register_product_type' );

/**
 * Register the product category taxonomy.
 */
function till_register_taxonomies() {
	register_taxonomy(
		'product_cat',
		'product',
		array(
			'labels'            => array(
				'name'          => __( 'Product Categories', 'till' ),
				'singular_name' => __( 'Product Category', 'till' ),
				'menu_name'     => __( 'Categories', 'till' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'product-category' ),
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'till_register_taxonomies' );

/* -------------------------------------------------------------------------
 * Price meta
 * ---------------------------------------------------------------------- */

/**
 * Add the Pricing & Inventory meta box.
 */
function till_add_meta_box() {
	add_meta_box( 'till_pricing', __( 'Pricing & Inventory', 'till' ), 'till_render_meta_box', 'product', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'till_add_meta_box' );

/**
 * Render the price meta box.
 *
 * @param WP_Post $post Current product.
 */
function till_render_meta_box( $post ) {
	wp_nonce_field( 'till_save_meta', 'till_meta_nonce' );
	$price   = get_post_meta( $post->ID, '_till_price', true );
	$sale    = get_post_meta( $post->ID, '_till_sale_price', true );
	$sku     = get_post_meta( $post->ID, '_till_sku', true );
	$stock   = get_post_meta( $post->ID, '_till_stock', true );
	$rating  = get_post_meta( $post->ID, '_till_rating', true );

	echo '<p><label for="till_price"><strong>' . esc_html__( 'Price', 'till' ) . '</strong></label><br />';
	echo '<input type="number" step="0.01" min="0" id="till_price" name="till_price" value="' . esc_attr( $price ) . '" style="width:100%" /></p>';

	echo '<p><label for="till_sale_price">' . esc_html__( 'Sale price (optional)', 'till' ) . '</label><br />';
	echo '<input type="number" step="0.01" min="0" id="till_sale_price" name="till_sale_price" value="' . esc_attr( $sale ) . '" style="width:100%" /></p>';

	echo '<p><label for="till_sku">' . esc_html__( 'SKU', 'till' ) . '</label><br />';
	echo '<input type="text" id="till_sku" name="till_sku" value="' . esc_attr( $sku ) . '" style="width:100%" /></p>';

	echo '<p><label for="till_stock">' . esc_html__( 'In stock', 'till' ) . '</label><br />';
	echo '<select id="till_stock" name="till_stock" style="width:100%">';
	echo '<option value="1"' . selected( $stock, '', false ) . selected( $stock, '1', false ) . '>' . esc_html__( 'In stock', 'till' ) . '</option>';
	echo '<option value="0"' . selected( $stock, '0', false ) . '>' . esc_html__( 'Sold out', 'till' ) . '</option>';
	echo '</select></p>';

	echo '<p><label for="till_rating">' . esc_html__( 'Rating (0–5)', 'till' ) . '</label><br />';
	echo '<input type="number" step="0.1" min="0" max="5" id="till_rating" name="till_rating" value="' . esc_attr( $rating ) . '" style="width:100%" /></p>';
}

/**
 * Persist the price meta.
 *
 * @param int $post_id Product ID.
 */
function till_save_meta( $post_id ) {
	if ( ! isset( $_POST['till_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['till_meta_nonce'] ), 'till_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'till_price'      => '_till_price',
		'till_sale_price' => '_till_sale_price',
		'till_sku'        => '_till_sku',
		'till_stock'      => '_till_stock',
		'till_rating'     => '_till_rating',
	);
	foreach ( $fields as $field => $meta ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post_product', 'till_save_meta' );

/**
 * Admin column showing the price in the Products list.
 *
 * @param array $cols Existing columns.
 * @return array
 */
function till_product_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['till_price'] = __( 'Price', 'till' );
		}
	}
	return $new;
}
add_filter( 'manage_product_posts_columns', 'till_product_columns' );

/**
 * Render the price admin column.
 *
 * @param string $col     Column key.
 * @param int    $post_id Product ID.
 */
function till_product_column( $col, $post_id ) {
	if ( 'till_price' === $col ) {
		echo esc_html( till_price( till_get_price( $post_id ) ) );
	}
}
add_action( 'manage_product_posts_custom_column', 'till_product_column', 10, 2 );

/* -------------------------------------------------------------------------
 * Read helpers
 * ---------------------------------------------------------------------- */

/**
 * The effective price (sale price when present, otherwise the regular price).
 *
 * @param int $id Product ID.
 * @return float
 */
function till_get_price( $id ) {
	$sale = get_post_meta( $id, '_till_sale_price', true );
	if ( '' !== $sale && (float) $sale > 0 ) {
		return (float) $sale;
	}
	return (float) get_post_meta( $id, '_till_price', true );
}

/**
 * The regular (pre-sale) price.
 *
 * @param int $id Product ID.
 * @return float
 */
function till_get_regular_price( $id ) {
	return (float) get_post_meta( $id, '_till_price', true );
}

/**
 * Whether this product is on sale.
 *
 * @param int $id Product ID.
 * @return bool
 */
function till_is_on_sale( $id ) {
	$sale = get_post_meta( $id, '_till_sale_price', true );
	return ( '' !== $sale && (float) $sale > 0 && (float) $sale < till_get_regular_price( $id ) );
}

/**
 * Whether this product is in stock (defaults to true).
 *
 * @param int $id Product ID.
 * @return bool
 */
function till_in_stock( $id ) {
	$stock = get_post_meta( $id, '_till_stock', true );
	return ( '0' !== (string) $stock );
}

/* -------------------------------------------------------------------------
 * Markup helpers
 * ---------------------------------------------------------------------- */

/**
 * A price block: shows the sale price next to a struck-through regular price
 * when the product is discounted.
 *
 * @param int $id Product ID.
 * @return string HTML.
 */
function till_price_html( $id ) {
	if ( till_is_on_sale( $id ) ) {
		return '<span class="till-price till-price--sale">'
			. '<del>' . esc_html( till_price( till_get_regular_price( $id ) ) ) . '</del> '
			. '<ins>' . esc_html( till_price( till_get_price( $id ) ) ) . '</ins>'
			. '</span>';
	}
	return '<span class="till-price">' . esc_html( till_price( till_get_price( $id ) ) ) . '</span>';
}

/**
 * A five-star rating row from the stored rating.
 *
 * @param int $id Product ID.
 * @return string HTML.
 */
function till_rating_html( $id ) {
	$rating = (float) get_post_meta( $id, '_till_rating', true );
	if ( $rating <= 0 ) {
		return '';
	}
	$full  = (int) floor( $rating );
	$stars = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$stars .= '<span class="till-star' . ( $i <= $full ? ' is-on' : '' ) . '" aria-hidden="true">&#9733;</span>';
	}
	return '<span class="till-rating" title="' . esc_attr( sprintf( __( '%s out of 5', 'till' ), number_format_i18n( $rating, 1 ) ) ) . '">'
		. $stars . '<span class="till-rating__num">' . esc_html( number_format_i18n( $rating, 1 ) ) . '</span></span>';
}

/**
 * A deterministic, license-clean placeholder gradient for products with no
 * featured image, so a fresh store never shows a broken frame.
 *
 * @param int $id Product ID.
 * @return string HTML.
 */
function till_placeholder( $id ) {
	$hue   = ( $id * 47 ) % 360;
	$style = sprintf(
		'background:linear-gradient(135deg,hsl(%1$d 42%% 90%%),hsl(%2$d 38%% 78%%));',
		$hue,
		( $hue + 40 ) % 360
	);
	$title   = wp_strip_all_tags( get_the_title( $id ) );
	$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
	return '<span class="till-thumb__ph" style="' . esc_attr( $style ) . '"><span>' . esc_html( $initial ) . '</span></span>';
}

/**
 * The product image (or placeholder), used by cards and the single page.
 *
 * @param int    $id   Product ID.
 * @param string $size Image size.
 * @return string HTML.
 */
function till_thumb( $id, $size = 'medium_large' ) {
	if ( has_post_thumbnail( $id ) ) {
		return get_the_post_thumbnail( $id, $size, array( 'class' => 'till-thumb__img', 'loading' => 'lazy', 'decoding' => 'async' ) );
	}
	return till_placeholder( $id );
}

/**
 * Render a single product card (used by the shop grid, featured rows, related).
 *
 * @param int $id Product ID.
 * @return string HTML.
 */
function till_product_card( $id ) {
	$permalink = get_permalink( $id );
	$on_sale   = till_is_on_sale( $id );
	$in_stock  = till_in_stock( $id );

	$badges = '';
	if ( $on_sale ) {
		$pct = till_get_regular_price( $id ) > 0
			? round( ( 1 - ( till_get_price( $id ) / till_get_regular_price( $id ) ) ) * 100 )
			: 0;
		$badges .= '<span class="till-badge till-badge--sale">' . esc_html( $pct ? '-' . $pct . '%' : __( 'Sale', 'till' ) ) . '</span>';
	}
	if ( ! $in_stock ) {
		$badges .= '<span class="till-badge till-badge--out">' . esc_html__( 'Sold out', 'till' ) . '</span>';
	}

	$cats     = get_the_terms( $id, 'product_cat' );
	$kicker   = ( $cats && ! is_wp_error( $cats ) ) ? esc_html( $cats[0]->name ) : '';
	$add_attr = $in_stock
		? 'data-till-add="' . esc_attr( $id ) . '"'
		: 'disabled';
	$add_label = $in_stock ? esc_html__( 'Add to cart', 'till' ) : esc_html__( 'Sold out', 'till' );

	$wishlist = apply_filters( 'till_card_corner', '', $id );

	return '<article class="till-card">'
		. '<a class="till-card__media" href="' . esc_url( $permalink ) . '">'
		. ( $badges ? '<span class="till-card__badges">' . $badges . '</span>' : '' )
		. $wishlist
		. '<span class="till-thumb">' . till_thumb( $id ) . '</span>'
		. '</a>'
		. '<div class="till-card__body">'
		. ( $kicker ? '<span class="till-card__cat">' . $kicker . '</span>' : '' )
		. '<h3 class="till-card__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>'
		. till_rating_html( $id )
		. '<div class="till-card__foot">'
		. till_price_html( $id )
		. '<button class="till-add till-btn" ' . $add_attr . ' aria-label="' . esc_attr( sprintf( __( 'Add %s to cart', 'till' ), get_the_title( $id ) ) ) . '">' . $add_label . '</button>'
		. '</div>'
		. '</div>'
		. '</article>';
}

/**
 * Render a responsive grid of product cards.
 *
 * @param int[] $ids Product IDs.
 * @return string HTML.
 */
function till_product_grid( $ids ) {
	if ( empty( $ids ) ) {
		return '<p class="till-empty">' . esc_html__( 'No products found.', 'till' ) . '</p>';
	}
	$out = '<div class="till-grid">';
	foreach ( $ids as $id ) {
		$out .= till_product_card( $id );
	}
	$out .= '</div>';
	return $out;
}

/* -------------------------------------------------------------------------
 * Single product
 * ---------------------------------------------------------------------- */

/**
 * Prepend a structured product summary (gallery, price, rating, add-to-cart)
 * above the product description on single product pages, in any theme.
 *
 * @param string $content Post content.
 * @return string
 */
function till_single_content( $content ) {
	if ( ! is_singular( 'product' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$id       = get_the_ID();
	$in_stock = till_in_stock( $id );
	$cats     = get_the_terms( $id, 'product_cat' );
	$kicker   = ( $cats && ! is_wp_error( $cats ) ) ? esc_html( $cats[0]->name ) : '';
	$sku      = get_post_meta( $id, '_till_sku', true );

	$summary  = '<div class="till-single">';
	$summary .= '<div class="till-single__media">';
	if ( till_is_on_sale( $id ) ) {
		$summary .= '<span class="till-badge till-badge--sale">' . esc_html__( 'Sale', 'till' ) . '</span>';
	}
	$summary .= '<span class="till-thumb">' . till_thumb( $id, 'large' ) . '</span>';
	$summary .= '</div>';

	$summary .= '<div class="till-single__info">';
	if ( $kicker ) {
		$summary .= '<span class="till-single__cat">' . $kicker . '</span>';
	}
	$summary .= '<h1 class="till-single__title">' . esc_html( get_the_title( $id ) ) . '</h1>';
	$summary .= till_rating_html( $id );
	$summary .= '<div class="till-single__price">' . till_price_html( $id ) . '</div>';

	$excerpt = get_the_excerpt( $id );
	if ( $excerpt ) {
		$summary .= '<p class="till-single__lead">' . esc_html( $excerpt ) . '</p>';
	}

	$summary .= '<div class="till-single__buy">';
	if ( $in_stock ) {
		$summary .= '<div class="till-qty"><button type="button" class="till-qty__btn" data-till-qty="-1" aria-label="' . esc_attr__( 'Decrease quantity', 'till' ) . '">&minus;</button>';
		$summary .= '<input class="till-qty__input" type="number" min="1" value="1" aria-label="' . esc_attr__( 'Quantity', 'till' ) . '" />';
		$summary .= '<button type="button" class="till-qty__btn" data-till-qty="1" aria-label="' . esc_attr__( 'Increase quantity', 'till' ) . '">+</button></div>';
		$summary .= '<button class="till-btn till-btn--lg till-add" data-till-add="' . esc_attr( $id ) . '" data-till-qty-source="1">' . esc_html__( 'Add to cart', 'till' ) . '</button>';
	} else {
		$summary .= '<button class="till-btn till-btn--lg" disabled>' . esc_html__( 'Sold out', 'till' ) . '</button>';
	}
	$summary .= apply_filters( 'till_single_buy', '', $id );
	$summary .= '</div>';

	$meta = array();
	if ( $sku ) {
		$meta[] = '<span class="till-single__sku">' . esc_html__( 'SKU:', 'till' ) . ' ' . esc_html( $sku ) . '</span>';
	}
	if ( $kicker ) {
		$meta[] = '<span>' . esc_html__( 'Category:', 'till' ) . ' ' . $kicker . '</span>';
	}
	$meta[] = '<span class="till-single__stock' . ( $in_stock ? ' is-in' : ' is-out' ) . '">' . ( $in_stock ? esc_html__( 'In stock', 'till' ) : esc_html__( 'Out of stock', 'till' ) ) . '</span>';
	$summary .= '<div class="till-single__meta">' . implode( '', $meta ) . '</div>';

	$summary .= '</div></div>';

	$description = $content ? '<div class="till-single__desc"><h2>' . esc_html__( 'Details', 'till' ) . '</h2>' . $content . '</div>' : '';

	// Related products from the same category.
	$related = till_related_products( $id );

	return $summary . $description . $related;
}
add_filter( 'the_content', 'till_single_content' );

/**
 * Up to four related products from the same category.
 *
 * @param int $id Current product.
 * @return string HTML.
 */
function till_related_products( $id ) {
	$cats = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'ids' ) );
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return '';
	}
	$q = new WP_Query(
		array(
			'post_type'           => 'product',
			'posts_per_page'      => 4,
			'post__not_in'        => array( $id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $cats,
				),
			),
		)
	);
	if ( ! $q->have_posts() ) {
		return '';
	}
	$ids = wp_list_pluck( $q->posts, 'ID' );
	wp_reset_postdata();

	return '<section class="till-related"><h2 class="till-related__title">' . esc_html__( 'You might also like', 'till' ) . '</h2>' . till_product_grid( $ids ) . '</section>';
}
