<?php
/**
 * Hearth theme setup and assets.
 *
 * @package Hearth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'HEARTH_VERSION' ) ) {
	define( 'HEARTH_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'hearth_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function hearth_setup() {
		load_theme_textdomain( 'hearth', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 80,
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
		add_theme_support( 'custom-background', array(
			'default-color' => 'f6ede0',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the homepage hero cover.
		add_image_size( 'hearth-hero', 1100, 1375, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'hearth' ),
			'social'  => esc_html__( 'Footer Menu', 'hearth' ),
		) );
	}
}
add_action( 'after_setup_theme', 'hearth_setup' );

/**
 * Set the content width in pixels.
 */
function hearth_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'hearth_content_width', 720 );
}
add_action( 'after_setup_theme', 'hearth_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function hearth_assets() {
	wp_enqueue_style( 'hearth-style', get_stylesheet_uri(), array(), HEARTH_VERSION );

	wp_enqueue_script(
		'hearth-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		HEARTH_VERSION,
		true
	);

	// Motion system: gentle warm fade-up reveals, the open/closed hours pip,
	// and a single-post reading-progress bar. Deferred, footer-loaded, and
	// fully gated behind prefers-reduced-motion inside the script itself.
	wp_enqueue_script(
		'hearth-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		HEARTH_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'hearth_assets' );

/**
 * Add the `defer` attribute to Hearth's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function hearth_defer_scripts( $tag, $handle ) {
	$deferred = array( 'hearth-navigation', 'hearth-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'hearth_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area.
 */
function hearth_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'hearth' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside posts and pages.', 'hearth' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'hearth_widgets_init' );

/**
 * Use an ellipsis reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function hearth_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'hearth_excerpt_more' );

/**
 * A short, appetizing excerpt suits the menu-card layout.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function hearth_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 26;
}
add_filter( 'excerpt_length', 'hearth_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'hearth_posted_meta' ) ) {
	function hearth_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'by %1$s', 'hearth' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the post's primary category as a pill link. Falls back to nothing
 * when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'hearth_category_pill' ) ) {
	function hearth_category_pill() {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return;
		}

		$category = $categories[0];
		printf(
			'<a class="entry-cat" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);
	}
}

/**
 * The seven daily service windows shown in the "Today's hours" card.
 *
 * Filterable so a child theme or a small plugin can wire real opening hours
 * in without touching templates. Each entry is [ label, hours-string,
 * open-minute, close-minute ] where the minutes are minutes-from-midnight in
 * the site's local timezone, or null for a closed day.
 *
 * @return array<int,array<string,mixed>>
 */
if ( ! function_exists( 'hearth_service_hours' ) ) {
	function hearth_service_hours() {
		// Index 0 = Sunday … 6 = Saturday, matching wp_date( 'w' ).
		$default = array(
			0 => array( 'label' => esc_html__( 'Sunday', 'hearth' ),    'hours' => esc_html__( '9:00 – 14:00', 'hearth' ),  'open' => 540,  'close' => 840 ),
			1 => array( 'label' => esc_html__( 'Monday', 'hearth' ),    'hours' => esc_html__( 'Closed', 'hearth' ),       'open' => null, 'close' => null ),
			2 => array( 'label' => esc_html__( 'Tuesday', 'hearth' ),   'hours' => esc_html__( '8:00 – 16:00', 'hearth' ),  'open' => 480,  'close' => 960 ),
			3 => array( 'label' => esc_html__( 'Wednesday', 'hearth' ), 'hours' => esc_html__( '8:00 – 16:00', 'hearth' ),  'open' => 480,  'close' => 960 ),
			4 => array( 'label' => esc_html__( 'Thursday', 'hearth' ),  'hours' => esc_html__( '8:00 – 22:00', 'hearth' ),  'open' => 480,  'close' => 1320 ),
			5 => array( 'label' => esc_html__( 'Friday', 'hearth' ),    'hours' => esc_html__( '8:00 – 22:00', 'hearth' ),  'open' => 480,  'close' => 1320 ),
			6 => array( 'label' => esc_html__( 'Saturday', 'hearth' ),  'hours' => esc_html__( '9:00 – 22:00', 'hearth' ),  'open' => 540,  'close' => 1320 ),
		);

		return apply_filters( 'hearth_service_hours', $default );
	}
}

/**
 * Render Hearth's signature "Today's hours" card.
 *
 * Computes open/closed status server-side from the site's own timezone, so the
 * status is correct even before JS runs (and with JS off). motion.js then adds
 * the gentle pulse to the "open" pip; the card is fully readable without it.
 *
 * @param bool $in_hero Whether the card is rendered inside the hero (adds the
 *                      reveal hook + hero positioning class scope).
 */
if ( ! function_exists( 'hearth_hours_card' ) ) {
	function hearth_hours_card( $in_hero = false ) {
		$hours = hearth_service_hours();
		if ( empty( $hours ) ) {
			return;
		}

		$today_index = (int) wp_date( 'w' );
		$now_minutes = (int) wp_date( 'G' ) * 60 + (int) wp_date( 'i' );

		$today = isset( $hours[ $today_index ] ) ? $hours[ $today_index ] : reset( $hours );

		$is_open = (
			null !== $today['open'] &&
			null !== $today['close'] &&
			$now_minutes >= (int) $today['open'] &&
			$now_minutes < (int) $today['close']
		);

		$status_class = $is_open ? '' : 'is-closed';
		$status_text  = $is_open ? esc_html__( 'Open now', 'hearth' ) : esc_html__( 'Closed', 'hearth' );

		$reveal = $in_hero ? ' data-hearth-reveal' : '';

		printf(
			'<aside class="hours-card"%1$s aria-label="%2$s">',
			$reveal, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute string above.
			esc_attr__( 'Opening hours', 'hearth' )
		);

		echo '<div class="hours-card__head">';
		printf( '<p class="hours-card__label">%s</p>', esc_html__( "Today's hours", 'hearth' ) );
		printf(
			'<span class="hours-status %1$s"><span class="hours-status__pip" aria-hidden="true"></span>%2$s</span>',
			esc_attr( $status_class ),
			esc_html( $status_text )
		);
		echo '</div>';

		printf(
			'<p class="hours-card__today"><span>%1$s</span>%2$s</p>',
			esc_html( $today['label'] ),
			esc_html( $today['hours'] )
		);

		echo '<ul class="hours-card__list">';
		foreach ( $hours as $index => $row ) {
			$row_class = ( $index === $today_index ) ? ' is-today' : '';
			printf(
				'<li class="hours-card__row%1$s"><span class="hours-card__day">%2$s</span><span class="hours-card__hrs">%3$s</span></li>',
				esc_attr( $row_class ),
				esc_html( $row['label'] ),
				esc_html( $row['hours'] )
			);
		}
		echo '</ul>';

		echo '</aside>';
	}
}

/**
 * Output a featured image, or a graceful warm gradient placeholder when a post
 * has none, so the menu grid and single never break their rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold hero cover on the home page so it
 *                      paints immediately instead of lazy-loading.
 */
if ( ! function_exists( 'hearth_featured_media' ) ) {
	function hearth_featured_media( $size = 'large', $link = false, $eager = false ) {
		$has_image = has_post_thumbnail();

		// Deterministic warm hue from the post ID so placeholders feel intentional
		// while staying in the appetizing terracotta→amber range.
		$hue   = (int) ( get_the_ID() * 23 ) % 36; // 0..35 around the terracotta base
		$style = sprintf( '--hearth-hue:%1$d;', $hue + 14 );

		$classes  = 'entry-media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		$inner = '';
		if ( $has_image ) {
			$load_eager = ( $eager || is_singular() );

			$img_attr = array(
				'class'    => 'entry-media__img',
				'loading'  => $load_eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			);

			if ( $eager ) {
				$img_attr['fetchpriority'] = 'high';
			}

			$inner = get_the_post_thumbnail( null, $size, $img_attr );
		} else {
			$title   = wp_strip_all_tags( get_the_title() );
			$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
			$inner   = '<span class="entry-media__glyph" aria-hidden="true">';
			$inner  .= esc_html( $initial );
			$inner  .= '</span>';
		}

		if ( $link && ! is_singular() ) {
			printf(
				'<a class="%1$s" style="%2$s" href="%3$s" tabindex="-1" aria-hidden="true">%4$s</a>',
				esc_attr( $classes ),
				esc_attr( $style ),
				esc_url( get_permalink() ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		} else {
			printf(
				'<div class="%1$s" style="%2$s">%3$s</div>',
				esc_attr( $classes ),
				esc_attr( $style ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		}
	}
}

/**
 * Output a dish-card cover (the menu-grid variant of the featured media).
 *
 * @param bool $eager Whether the first row should paint eagerly.
 */
if ( ! function_exists( 'hearth_dish_media' ) ) {
	function hearth_dish_media( $eager = false ) {
		$has_image = has_post_thumbnail();

		$hue   = (int) ( get_the_ID() * 23 ) % 36;
		$style = sprintf( '--hearth-hue:%1$d;', $hue + 14 );

		$classes  = 'dish__media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		if ( $has_image ) {
			$img_attr = array(
				'class'    => 'dish__img',
				'loading'  => $eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			);
			if ( $eager ) {
				$img_attr['fetchpriority'] = 'high';
			}
			$inner = get_the_post_thumbnail( null, 'medium_large', $img_attr );
		} else {
			$title   = wp_strip_all_tags( get_the_title() );
			$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
			$inner   = '<span class="dish__glyph" aria-hidden="true">' . esc_html( $initial ) . '</span>';
		}

		printf(
			'<a class="%1$s" style="%2$s" href="%3$s" tabindex="-1" aria-hidden="true">%4$s</a>',
			esc_attr( $classes ),
			esc_attr( $style ),
			esc_url( get_permalink() ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
		);
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function hearth_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'hearth-home';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'hearth-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'hearth_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
