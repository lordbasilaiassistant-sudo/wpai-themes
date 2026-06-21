<?php
/**
 * Almanac theme setup and assets.
 *
 * @package Almanac
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ALMANAC_VERSION' ) ) {
	define( 'ALMANAC_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'almanac_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function almanac_setup() {
		load_theme_textdomain( 'almanac', get_template_directory() . '/languages' );

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
			'default-color' => 'f4f1e9',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the homepage seedling (freshest note) cover.
		add_image_size( 'almanac-lead', 1320, 760, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'almanac' ),
			'social'  => esc_html__( 'Footer Menu', 'almanac' ),
		) );
	}
}
add_action( 'after_setup_theme', 'almanac_setup' );

/**
 * Set the content width in pixels.
 */
function almanac_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'almanac_content_width', 720 );
}
add_action( 'after_setup_theme', 'almanac_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function almanac_assets() {
	wp_enqueue_style( 'almanac-style', get_stylesheet_uri(), array(), ALMANAC_VERSION );

	wp_enqueue_script(
		'almanac-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		ALMANAC_VERSION,
		true
	);

	// Motion system: the signature "growing" reveal, the connective vine
	// sprout, scroll reveals, reading progress, and the word-by-word intro.
	// Deferred, footer-loaded, and fully gated behind prefers-reduced-motion
	// inside the script itself.
	wp_enqueue_script(
		'almanac-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		ALMANAC_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'almanac_assets' );

/**
 * Add the `defer` attribute to Almanac's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function almanac_defer_scripts( $tag, $handle ) {
	$deferred = array( 'almanac-navigation', 'almanac-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'almanac_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area.
 */
function almanac_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'almanac' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside notes and pages — the stacks.', 'almanac' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'almanac_widgets_init' );

/**
 * Use a quiet ellipsis reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function almanac_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'almanac_excerpt_more' );

/**
 * A slightly shorter excerpt suits the note-card layout.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function almanac_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 30;
}
add_filter( 'excerpt_length', 'almanac_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'almanac_posted_meta' ) ) {
	function almanac_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'by %1$s', 'almanac' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the evergreen "tended" stamps: when a note was planted (published) and
 * when it was last tended (modified). The garden conceit, made literal — only
 * showing "last tended" when the note has actually been revised since planting.
 *
 * @param bool $show_planted Whether to print the "Planted" stamp.
 */
if ( ! function_exists( 'almanac_tended_meta' ) ) {
	function almanac_tended_meta( $show_planted = true ) {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		if ( $show_planted ) {
			printf(
				'<span class="tended tended--planted"><span class="tended__dot" aria-hidden="true"></span>%1$s <time datetime="%2$s">%3$s</time></span>',
				esc_html__( 'Planted', 'almanac' ),
				esc_attr( get_the_date( DATE_W3C ) ),
				esc_html( get_the_date() )
			);
		}

		// Only call it "tended" when the note was meaningfully revised after it
		// was first planted (more than a day later, to ignore the publish race).
		$published = (int) get_the_time( 'U' );
		$modified  = (int) get_the_modified_time( 'U' );

		if ( ( $modified - $published ) > DAY_IN_SECONDS ) {
			printf(
				'<span class="tended tended--updated"><span class="tended__dot" aria-hidden="true"></span>%1$s <time datetime="%2$s">%3$s</time></span>',
				esc_html__( 'Last tended', 'almanac' ),
				esc_attr( get_the_modified_date( DATE_W3C ) ),
				esc_html( get_the_modified_date() )
			);
		}
	}
}

/**
 * Print the post's primary category as a "patch" pill link. Falls back to
 * nothing when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'almanac_category_pill' ) ) {
	function almanac_category_pill() {
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
 * Print a compact, inline tag row for a note card — visible tags everywhere is
 * a core part of the digital-garden concept. Capped so cards stay tidy.
 *
 * @param int $max Maximum number of tags to show.
 */
if ( ! function_exists( 'almanac_tag_row' ) ) {
	function almanac_tag_row( $max = 4 ) {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		$tags = get_the_tags();
		if ( empty( $tags ) || is_wp_error( $tags ) ) {
			return;
		}

		$tags = array_slice( $tags, 0, $max );

		echo '<ul class="entry-tagrow" aria-label="' . esc_attr__( 'Tags', 'almanac' ) . '">';
		foreach ( $tags as $tag ) {
			printf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( get_tag_link( $tag->term_id ) ),
				esc_html( $tag->name )
			);
		}
		echo '</ul>';
	}
}

/**
 * Print Almanac's signature connective "vine sprout".
 *
 * A short inline-SVG stem with a single leaf. It renders statically as a quiet
 * verdigris flourish; when JS is on (and motion is allowed) motion.js grows it
 * — the stem draws up from the baseline, then the leaf unfurls — the moment it
 * scrolls into view. The element is decorative, so it is hidden from AT.
 *
 * @param string $variant Optional modifier ('intro') for sizing.
 */
if ( ! function_exists( 'almanac_sprout' ) ) {
	function almanac_sprout( $variant = '' ) {
		$class = 'alm-sprout';
		if ( $variant ) {
			$class .= ' alm-sprout--' . sanitize_html_class( $variant );
		}

		// viewBox 0 0 48 30. A stem rising from the bottom-left, with one leaf
		// curving off to the right — a seedling unfurling.
		$stem = 'M6 30 C6 20 8 12 14 6';
		$leaf = 'M14 7 C22 4 32 7 38 12 C30 13 22 12 15 16';

		printf(
			'<svg class="%1$s" viewBox="0 0 48 30" fill="none" aria-hidden="true" focusable="false">' .
				'<path class="alm-sprout__stem" d="%2$s" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>' .
				'<path class="alm-sprout__leaf" d="%3$s" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>' .
			'</svg>',
			esc_attr( $class ),
			esc_attr( $stem ),
			esc_attr( $leaf )
		);
	}
}

/**
 * Output the small leaf-mark used beside the wordmark and in the footer.
 * Decorative inline SVG; hidden from assistive tech.
 *
 * @param string $class Extra class for sizing context.
 */
if ( ! function_exists( 'almanac_leaf_mark' ) ) {
	function almanac_leaf_mark( $class = 'site-branding__mark' ) {
		printf(
			'<svg class="%1$s" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false">' .
				'<path d="M16 29 C16 20 16 13 16 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' .
				'<path d="M16 16 C10 16 6 13 5 7 C12 6 16 9 17 15" fill="currentColor" opacity="0.85"/>' .
				'<path d="M16 12 C22 12 26 9 27 4 C20 3 16 6 15 11" fill="currentColor" opacity="0.55"/>' .
			'</svg>',
			esc_attr( $class )
		);
	}
}

/**
 * Output a featured image, or a graceful verdigris-tinted "garden paper"
 * placeholder when a note has none, so the index and single never break their
 * rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold seedling cover on the home page
 *                      so it paints immediately instead of lazy-loading.
 */
if ( ! function_exists( 'almanac_featured_media' ) ) {
	function almanac_featured_media( $size = 'large', $link = false, $eager = false ) {
		$has_image = has_post_thumbnail();

		// Deterministic hue from the post ID, kept near the verdigris family so
		// placeholders feel intentional and on-brand (greens, teals, mosses).
		$hue   = 120 + ( (int) ( get_the_ID() * 37 ) % 80 ); // 120..200
		$style = sprintf( '--alm-hue:%1$d;', $hue );

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
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function almanac_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'almanac-garden';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'almanac-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'almanac_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
