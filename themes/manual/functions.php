<?php
/**
 * Manual theme setup and assets.
 *
 * @package Manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MANUAL_VERSION' ) ) {
	define( 'MANUAL_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'manual_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function manual_setup() {
		load_theme_textdomain( 'manual', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 64,
			'width'       => 64,
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
			'default-color' => 'f6f8fa',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the docs-home lead doc cover.
		add_image_size( 'manual-lead', 1200, 720, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'manual' ),
			'docs'    => esc_html__( 'Docs Navigation (left rail)', 'manual' ),
			'social'  => esc_html__( 'Footer Menu', 'manual' ),
		) );
	}
}
add_action( 'after_setup_theme', 'manual_setup' );

/**
 * Set the content width in pixels.
 */
function manual_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'manual_content_width', 760 );
}
add_action( 'after_setup_theme', 'manual_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function manual_assets() {
	wp_enqueue_style( 'manual-style', get_stylesheet_uri(), array(), MANUAL_VERSION );

	wp_enqueue_script(
		'manual-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		MANUAL_VERSION,
		true
	);

	// Motion + signature system: scroll reveals, the "On this page" rail with
	// active-section tracking, smooth anchor scrolling, reading progress, and
	// code-block copy buttons. Deferred, footer-loaded, gated behind
	// prefers-reduced-motion inside the script itself.
	wp_enqueue_script(
		'manual-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		MANUAL_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'manual_assets' );

/**
 * Add the `defer` attribute to Manual's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function manual_defer_scripts( $tag, $handle ) {
	$deferred = array( 'manual-navigation', 'manual-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'manual_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area. (Optional — the docs rail is the default
 * left navigation; this is for sites that prefer classic widgets.)
 */
function manual_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'manual' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Optional widgets. When active, these appear in the left rail instead of the docs navigation tree.', 'manual' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'manual_widgets_init' );

/**
 * Use a concise ellipsis reading prompt instead of the bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function manual_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'manual_excerpt_more' );

/**
 * A compact excerpt suits the documentation card grid.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function manual_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 26;
}
add_filter( 'excerpt_length', 'manual_excerpt_length' );

/**
 * Print human-readable post meta (byline + date + read context).
 */
if ( ! function_exists( 'manual_posted_meta' ) ) {
	function manual_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'Updated by %1$s', 'manual' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the post's primary category as a hashtag-style pill link. Falls back to
 * nothing when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'manual_category_pill' ) ) {
	function manual_category_pill() {
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
 * Print the documentation "version" label shown beside the site title.
 *
 * Reads a Customizer theme mod so site owners can set their docs version (e.g.
 * "v2.4" or "stable"). Decorative chrome; rendered only when set.
 */
if ( ! function_exists( 'manual_version_label' ) ) {
	function manual_version_label() {
		$version = get_theme_mod( 'manual_version_label', 'v1.0' );
		$version = trim( (string) $version );

		if ( '' === $version ) {
			return;
		}

		printf(
			'<span class="site-version" title="%1$s">%2$s</span>',
			esc_attr__( 'Documentation version', 'manual' ),
			esc_html( $version )
		);
	}
}

/**
 * The small book/manual mark used in place of a logo when none is set.
 * Decorative; hidden from assistive tech.
 */
if ( ! function_exists( 'manual_site_mark' ) ) {
	function manual_site_mark() {
		echo '<span class="site-mark" aria-hidden="true">';
		echo '<svg viewBox="0 0 24 24" fill="none" focusable="false">';
		echo '<path d="M5 4.5A1.5 1.5 0 0 1 6.5 3H19v15.5H6.5A1.5 1.5 0 0 0 5 20V4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>';
		echo '<path d="M5 20a1.5 1.5 0 0 0 1.5 1.5H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>';
		echo '<path d="M9 7.5h6M9 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>';
		echo '</svg>';
		echo '</span>';
	}
}

/**
 * Output a featured image, or a graceful blueprint-grid placeholder when a post
 * has none, so the docs grid and single never break their rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold lead/hero cover on the docs home.
 */
if ( ! function_exists( 'manual_featured_media' ) ) {
	function manual_featured_media( $size = 'large', $link = false, $eager = false ) {
		$has_image = has_post_thumbnail();

		// Deterministic hue from the post ID so placeholders feel intentional.
		// Bias toward the cool blue-teal range that suits a documentation site.
		$hue   = 180 + ( (int) ( get_the_ID() * 31 ) % 70 );
		$style = sprintf( '--manual-hue:%1$d;', $hue );

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
 * Render the left docs-navigation rail.
 *
 * Prefers the dedicated "docs" menu when assigned (a manually-curated section
 * tree, exactly the docs-site feel). Otherwise it builds a sensible tree from
 * the site's pages and post categories so the rail is useful out of the box.
 */
if ( ! function_exists( 'manual_docs_nav' ) ) {
	function manual_docs_nav() {
		?>
		<nav class="docs-nav" aria-label="<?php esc_attr_e( 'Documentation', 'manual' ); ?>">
			<button class="docs-nav__toggle" aria-expanded="false" aria-controls="docs-nav-panel">
				<span><?php esc_html_e( 'Browse the docs', 'manual' ); ?></span>
				<span class="docs-nav__toggle-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
			</button>

			<div class="docs-nav__panel" id="docs-nav-panel">
				<div class="docs-nav__head">
					<p class="docs-nav__label"><?php esc_html_e( 'Contents', 'manual' ); ?></p>
					<?php
					$count = (int) wp_count_posts()->publish;
					if ( $count > 0 ) {
						printf( '<span class="docs-nav__count">%s</span>', esc_html( number_format_i18n( $count ) ) );
					}
					?>
				</div>

				<?php if ( has_nav_menu( 'docs' ) ) : ?>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'docs',
						'menu_class'     => 'docs-nav__list',
						'container'      => false,
						'fallback_cb'    => false,
						'depth'          => 3,
					) );
					?>
				<?php else : ?>
					<?php manual_docs_nav_fallback(); ?>
				<?php endif; ?>
			</div>
		</nav>
		<?php
	}
}

/**
 * A graceful default docs tree built from categories (with their posts) and
 * top-level pages, so the left rail is immediately useful with no menu set up.
 */
if ( ! function_exists( 'manual_docs_nav_fallback' ) ) {
	function manual_docs_nav_fallback() {
		$categories = get_categories( array(
			'orderby'      => 'name',
			'hide_empty'   => true,
			'number'       => 8,
		) );

		if ( ! empty( $categories ) ) {
			echo '<div class="docs-nav__group">';
			echo '<p class="docs-nav__group-title">' . esc_html__( 'Sections', 'manual' ) . '</p>';

			foreach ( $categories as $category ) {
				echo '<ul class="docs-nav__list">';

				$current = ( is_category( $category->term_id ) ) ? ' class="current-menu-item"' : '';
				printf(
					'<li%1$s><a href="%2$s">%3$s</a>',
					$current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static class string above.
					esc_url( get_category_link( $category->term_id ) ),
					esc_html( $category->name )
				);

				$posts = get_posts( array(
					'category'    => $category->term_id,
					'numberposts' => 6,
					'orderby'     => 'title',
					'order'       => 'ASC',
				) );

				if ( ! empty( $posts ) ) {
					echo '<ul>';
					foreach ( $posts as $post_item ) {
						$is_current = ( is_single( $post_item->ID ) ) ? ' class="current-menu-item"' : '';
						printf(
							'<li%1$s><a href="%2$s">%3$s</a></li>',
							$is_current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static class string above.
							esc_url( get_permalink( $post_item->ID ) ),
							esc_html( get_the_title( $post_item->ID ) )
						);
					}
					echo '</ul>';
				}

				echo '</li></ul>';
			}

			echo '</div>';
		}

		$pages = get_pages( array(
			'parent'      => 0,
			'sort_column' => 'menu_order,post_title',
			'number'      => 8,
		) );

		if ( ! empty( $pages ) ) {
			echo '<div class="docs-nav__group">';
			echo '<p class="docs-nav__group-title">' . esc_html__( 'Pages', 'manual' ) . '</p>';
			echo '<ul class="docs-nav__list">';
			foreach ( $pages as $page_item ) {
				$is_current = ( is_page( $page_item->ID ) ) ? ' class="current_page_item"' : '';
				printf(
					'<li%1$s><a href="%2$s">%3$s</a></li>',
					$is_current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static class string above.
					esc_url( get_permalink( $page_item->ID ) ),
					esc_html( $page_item->post_title )
				);
			}
			echo '</ul></div>';
		}

		if ( empty( $categories ) && empty( $pages ) ) {
			echo '<p class="docs-nav__group-title">' . esc_html__( 'No sections yet', 'manual' ) . '</p>';
		}
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function manual_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'manual-docs-home';
	}
	if ( is_singular() ) {
		$classes[] = 'manual-doc';
	}
	return $classes;
}
add_filter( 'body_class', 'manual_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
