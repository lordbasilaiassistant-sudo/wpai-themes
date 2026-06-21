<?php
/**
 * Atelier theme setup and assets.
 *
 * @package Atelier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ATELIER_VERSION' ) ) {
	define( 'ATELIER_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'atelier_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function atelier_setup() {
		load_theme_textdomain( 'atelier', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 72,
			'width'       => 72,
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
			'default-color' => 'f4f1ea',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the homepage feature-plate cover.
		add_image_size( 'atelier-feature', 1440, 900, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'atelier' ),
			'social'  => esc_html__( 'Footer Menu', 'atelier' ),
		) );
	}
}
add_action( 'after_setup_theme', 'atelier_setup' );

/**
 * Set the content width in pixels.
 */
function atelier_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'atelier_content_width', 760 );
}
add_action( 'after_setup_theme', 'atelier_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function atelier_assets() {
	wp_enqueue_style( 'atelier-style', get_stylesheet_uri(), array(), ATELIER_VERSION );

	wp_enqueue_script(
		'atelier-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		ATELIER_VERSION,
		true
	);

	// Motion system: scroll reveals, the signature clip-path image reveal, the
	// cursor-following caption, and the studio-statement word reveal. Deferred,
	// footer-loaded, and fully gated behind prefers-reduced-motion in-script.
	wp_enqueue_script(
		'atelier-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		ATELIER_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'atelier_assets' );

/**
 * Add the `defer` attribute to Atelier's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function atelier_defer_scripts( $tag, $handle ) {
	$deferred = array( 'atelier-navigation', 'atelier-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'atelier_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area.
 */
function atelier_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'atelier' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside posts, pages, and the project archive.', 'atelier' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'atelier_widgets_init' );

/**
 * Use a quiet ellipsis reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function atelier_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'atelier_excerpt_more' );

/**
 * A short, gallery-caption excerpt suits the project grid.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function atelier_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 26;
}
add_filter( 'excerpt_length', 'atelier_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'atelier_posted_meta' ) ) {
	function atelier_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'by %1$s', 'atelier' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the post's primary category as an underlined kicker link. Falls back
 * to nothing when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'atelier_category_pill' ) ) {
	function atelier_category_pill() {
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
 * Output a featured image, or a graceful tonal placeholder when a post has
 * none, so the project grid and single posts never break their rhythm.
 *
 * The returned media frame carries `data-atelier-clip` so motion.js can run
 * the signature clip-path reveal, and `data-atelier-caption` so the
 * cursor-following caption knows what to name.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold feature/hero cover on the home.
 * @param string $index Optional frame index string drawn in the corner (e.g.
 *                      "01"). Empty to omit.
 */
if ( ! function_exists( 'atelier_featured_media' ) ) {
	function atelier_featured_media( $size = 'large', $link = false, $eager = false, $index = '' ) {
		$has_image = has_post_thumbnail();

		// Deterministic hue from the post ID so placeholders feel intentional
		// while staying within the theme's narrow, low-saturation neutral range.
		$hue   = (int) ( get_the_ID() * 37 ) % 360;
		$style = sprintf( '--at-hue:%1$d;', $hue );

		$classes  = 'entry-media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		$inner = '';

		if ( '' !== $index ) {
			$inner .= '<span class="entry-media__index" aria-hidden="true">' . esc_html( $index ) . '</span>';
		}

		if ( $has_image ) {
			$load_eager = ( $eager || is_singular() );

			$img_attr = array(
				'class'    => 'entry-media__img',
				'loading'  => $load_eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			);

			// The above-the-fold hero needs high fetch priority so it paints
			// first; everything else stays lazy with default priority.
			if ( $eager ) {
				$img_attr['fetchpriority'] = 'high';
			}

			$inner .= get_the_post_thumbnail( null, $size, $img_attr );
		} else {
			$title   = wp_strip_all_tags( get_the_title() );
			$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
			$inner  .= '<span class="entry-media__glyph" aria-hidden="true">';
			$inner  .= esc_html( $initial );
			$inner  .= '</span>';
		}

		$caption = wp_strip_all_tags( get_the_title() );

		if ( $link && ! is_singular() ) {
			printf(
				'<a class="%1$s" style="%2$s" href="%3$s" data-atelier-clip data-atelier-caption="%4$s" tabindex="-1" aria-hidden="true">%5$s</a>',
				esc_attr( $classes ),
				esc_attr( $style ),
				esc_url( get_permalink() ),
				esc_attr( $caption ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		} else {
			printf(
				'<div class="%1$s" style="%2$s" data-atelier-clip>%3$s</div>',
				esc_attr( $classes ),
				esc_attr( $style ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		}
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function atelier_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'atelier-home';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'atelier-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'atelier_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
