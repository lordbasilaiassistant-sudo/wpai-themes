<?php
/**
 * Emporium theme setup and assets.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EMPORIUM_VERSION' ) ) {
	define( 'EMPORIUM_VERSION', '1.0.0' );
}

require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'emporium_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function emporium_setup() {
		load_theme_textdomain( 'emporium', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 60,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );

		// Native WPAI companion-plugin placement hook support.
		add_theme_support( 'wpai-companions' );

		add_image_size( 'emporium-hero', 1200, 960, true );
		add_image_size( 'emporium-card', 800, 600, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'emporium' ),
			'footer'  => esc_html__( 'Footer Menu', 'emporium' ),
		) );
	}
}
add_action( 'after_setup_theme', 'emporium_setup' );

/**
 * Set the content width in pixels.
 */
function emporium_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'emporium_content_width', 760 );
}
add_action( 'after_setup_theme', 'emporium_content_width', 0 );

/**
 * Enqueue styles and scripts. The stylesheet declares a dependency on Till's
 * stylesheet when Till is active, so Emporium's CSS variables win the cascade
 * and recolour the whole store to match the theme.
 */
function emporium_assets() {
	$deps = array();
	if ( wp_style_is( 'till', 'enqueued' ) || wp_style_is( 'till', 'registered' ) ) {
		$deps[] = 'till';
	}
	if ( wp_style_is( 'keepsake', 'enqueued' ) || wp_style_is( 'keepsake', 'registered' ) ) {
		$deps[] = 'keepsake';
	}

	wp_enqueue_style( 'emporium-style', get_stylesheet_uri(), $deps, EMPORIUM_VERSION );

	wp_enqueue_script(
		'emporium-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		EMPORIUM_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'emporium_assets', 20 );

/**
 * Register the sidebar widget area.
 */
function emporium_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Journal Sidebar', 'emporium' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside journal posts and archives.', 'emporium' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'emporium_widgets_init' );

/* -------------------------------------------------------------------------
 * Store awareness — graceful whether or not Till — Commerce is active.
 * ---------------------------------------------------------------------- */

/**
 * Whether the Till — Commerce plugin is active.
 *
 * @return bool
 */
function emporium_has_store() {
	return function_exists( 'till_cart_button' ) && post_type_exists( 'product' );
}

/**
 * Print the header cart button (Till) when the store is active.
 */
function emporium_cart_button() {
	if ( function_exists( 'till_cart_button' ) ) {
		echo till_cart_button( esc_html__( 'Cart', 'emporium' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled & escaped in Till.
	}
}

/**
 * Print the header wishlist link (Keepsake) when active.
 */
function emporium_wishlist_link() {
	if ( function_exists( 'keepsake_count_link' ) ) {
		echo keepsake_count_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled & escaped in Keepsake.
	}
}

/**
 * The URL of the shop page (Till), falling back to the product archive or home.
 *
 * @return string
 */
function emporium_shop_url() {
	if ( function_exists( 'till_page_url' ) ) {
		return till_page_url( 'shop' );
	}
	if ( post_type_exists( 'product' ) ) {
		$link = get_post_type_archive_link( 'product' );
		if ( $link ) {
			return $link;
		}
	}
	return home_url( '/' );
}

/**
 * Featured products grid for the front page, using Till when present.
 *
 * @param int $count Number of products.
 * @return string HTML, or '' when the store is inactive.
 */
function emporium_featured_products( $count = 8 ) {
	if ( ! emporium_has_store() ) {
		return '';
	}
	$q = new WP_Query( array(
		'post_type'           => 'product',
		'posts_per_page'      => (int) $count,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	if ( ! $q->have_posts() ) {
		return '';
	}
	$ids = wp_list_pluck( $q->posts, 'ID' );
	wp_reset_postdata();
	if ( function_exists( 'till_product_grid' ) ) {
		return till_product_grid( $ids );
	}
	return '';
}

/**
 * Product categories with their first product's image, for the tile section.
 *
 * @param int $limit Maximum categories.
 * @return array<int,array<string,mixed>>
 */
function emporium_shop_categories( $limit = 4 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => $limit,
	) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$out = array();
	foreach ( $terms as $term ) {
		$thumb = '';
		$first = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		) );
		if ( $first && has_post_thumbnail( $first[0] ) ) {
			$thumb = get_the_post_thumbnail_url( $first[0], 'emporium-card' );
		}
		$out[] = array(
			'name'  => $term->name,
			'url'   => get_term_link( $term ),
			'count' => (int) $term->count,
			'thumb' => $thumb,
		);
	}
	return $out;
}

/**
 * A tasteful tonal placeholder for a journal card with no featured image.
 *
 * @param int $id Post ID.
 * @return string HTML.
 */
function emporium_post_placeholder( $id ) {
	$hue     = (int) ( $id * 41 ) % 360;
	$style   = sprintf( 'background:linear-gradient(135deg,hsl(%1$d 30%% 90%%),hsl(%2$d 28%% 80%%));', $hue, ( $hue + 36 ) % 360 );
	$title   = wp_strip_all_tags( get_the_title( $id ) );
	$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
	return '<span class="em-post-card__ph" style="' . esc_attr( $style ) . '">' . esc_html( $initial ) . '</span>';
}

/**
 * Render a journal post card (used on the front page and the blog index).
 */
function emporium_post_card() {
	$cats = get_the_category();
	?>
	<article class="em-post-card">
		<a class="em-post-card__media" href="<?php the_permalink(); ?>">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'emporium-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) );
			} else {
				echo emporium_post_placeholder( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			}
			?>
		</a>
		<?php if ( ! empty( $cats ) ) : ?>
			<a class="em-post-card__cat" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
		<?php endif; ?>
		<h3 class="em-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="em-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
		<p class="em-post-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
	</article>
	<?php
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function emporium_body_classes( $classes ) {
	if ( emporium_has_store() ) {
		$classes[] = 'has-store';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'emporium_body_classes' );

/**
 * A trimmer, shop-friendly excerpt length.
 *
 * @param int $length Default length.
 * @return int
 */
function emporium_excerpt_length( $length ) {
	return is_admin() ? $length : 24;
}
add_filter( 'excerpt_length', 'emporium_excerpt_length' );

require_once get_template_directory() . '/inc/companions.php';
