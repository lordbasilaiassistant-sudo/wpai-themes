<?php
/**
 * Plugin Name: Kindred — Related Posts
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: A tasteful "You might also like" section after single posts, with related posts chosen by shared categories, then tags, then recency. Theme-adaptive, accessible, cached, zero configuration.
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kindred-related
 *
 * @package Kindred
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const KINDRED_VERSION = '1.0.0';

/**
 * Number of related posts to display.
 *
 * Tunable via the `kindred_posts_count` filter.
 */
const KINDRED_COUNT = 3;

/**
 * Transient lifetime for the per-post related-ID cache (12 hours).
 */
const KINDRED_CACHE_TTL = 12 * HOUR_IN_SECONDS;

/**
 * Resolve how many related posts to show, honoring the filter.
 *
 * Falls back to the default if a filter returns a non-positive value, so a
 * misbehaving filter can never produce an empty or negative query.
 *
 * @return int Number of posts, always >= 1.
 */
function kindred_posts_count() {
	$count = (int) apply_filters( 'kindred_posts_count', KINDRED_COUNT );

	return $count > 0 ? $count : KINDRED_COUNT;
}

/**
 * The transient key used to cache related IDs for a given post.
 *
 * The desired count is folded into the key so changing it (via filter) never
 * serves a stale, wrong-length result, and a version bump invalidates old data.
 *
 * @param int $post_id The post whose related IDs are cached.
 * @return string Transient name.
 */
function kindred_cache_key( $post_id ) {
	return 'kindred_related_' . (int) $post_id . '_' . kindred_posts_count() . '_' . KINDRED_VERSION;
}

/**
 * Run a single related-posts query and return the matching post IDs.
 *
 * Tuned for speed: returns IDs only (`fields => 'ids'`), skips the found-rows
 * count (`no_found_rows`), and skips term/meta cache priming we don't need.
 * Always excludes the current post and limits to published posts.
 *
 * @param int   $post_id    The current post to find relatives for.
 * @param int   $count      How many IDs to request.
 * @param array $tax_query  Optional taxonomy query (categories or tags).
 * @param array $exclude    Post IDs to exclude in addition to the current one.
 * @return int[] Matching post IDs.
 */
function kindred_query_ids( $post_id, $count, $tax_query = array(), $exclude = array() ) {
	$args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => $count,
		'post__not_in'           => array_merge( array( (int) $post_id ), array_map( 'intval', $exclude ) ),
		'ignore_sticky_posts'    => true,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Intentional, results are cached in a transient.
	}

	$query = new WP_Query( $args );

	return array_map( 'intval', $query->posts );
}

/**
 * Compute the related post IDs for a post: shared categories, then shared tags,
 * then most-recent — de-duplicated and capped at the requested count.
 *
 * Strategy: collect by shared categories first (strongest signal), then top up
 * with shared tags, then top up with the most recent posts so the section is
 * always full on a site with enough content. Each pass excludes what we already
 * have so there are no duplicates.
 *
 * @param int $post_id The current post.
 * @return int[] Ordered, de-duplicated related post IDs (length <= count).
 */
function kindred_compute_related_ids( $post_id ) {
	$post_id = (int) $post_id;
	$count   = kindred_posts_count();
	$related = array();

	// Pass 1: posts that share a category.
	$category_ids = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
	if ( ! empty( $category_ids ) && ! is_wp_error( $category_ids ) ) {
		$related = kindred_query_ids(
			$post_id,
			$count,
			array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $category_ids ),
				),
			)
		);
	}

	// Pass 2: top up with posts that share a tag.
	if ( count( $related ) < $count ) {
		$tag_ids = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
		if ( ! empty( $tag_ids ) && ! is_wp_error( $tag_ids ) ) {
			$needed = $count - count( $related );
			$more   = kindred_query_ids(
				$post_id,
				$needed,
				array(
					array(
						'taxonomy' => 'post_tag',
						'field'    => 'term_id',
						'terms'    => array_map( 'intval', $tag_ids ),
					),
				),
				$related
			);
			$related = array_merge( $related, $more );
		}
	}

	// Pass 3: top up with the most recent posts (no taxonomy constraint).
	if ( count( $related ) < $count ) {
		$needed  = $count - count( $related );
		$more    = kindred_query_ids( $post_id, $needed, array(), $related );
		$related = array_merge( $related, $more );
	}

	// Final safety: unique, re-indexed, capped.
	$related = array_values( array_unique( array_map( 'intval', $related ) ) );

	return array_slice( $related, 0, $count );
}

/**
 * Get the related post IDs for a post, served from a transient when available.
 *
 * The result is cached for ~12 hours per post and invalidated on save_post
 * (see kindred_clear_cache). An empty result is cached too, so a post with no
 * relatives doesn't re-run three queries on every view.
 *
 * @param int $post_id The current post.
 * @return int[] Related post IDs.
 */
function kindred_get_related_ids( $post_id ) {
	$post_id = (int) $post_id;
	$key     = kindred_cache_key( $post_id );
	$cached  = get_transient( $key );

	// Transients store the array directly; we sentinel an empty result as a
	// string so a genuine "no relatives" answer is distinguishable from a miss.
	if ( is_array( $cached ) ) {
		return array_map( 'intval', $cached );
	}
	if ( 'none' === $cached ) {
		return array();
	}

	$related = kindred_compute_related_ids( $post_id );

	set_transient( $key, empty( $related ) ? 'none' : $related, KINDRED_CACHE_TTL );

	return $related;
}

/**
 * Invalidate the cached related IDs when a post is created or updated.
 *
 * Clears the saved post's own cache. Because relationships are symmetric and a
 * site's content shifts slowly, neighbor caches simply expire on their 12-hour
 * TTL — we avoid an expensive fan-out delete on every save.
 *
 * @param int $post_id The post being saved.
 * @return void
 */
function kindred_clear_cache( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	delete_transient( kindred_cache_key( $post_id ) );
}
add_action( 'save_post', 'kindred_clear_cache' );
add_action( 'deleted_post', 'kindred_clear_cache' );

/**
 * Build the featured-image (or graceful placeholder) markup for a card.
 *
 * The image is rendered with an empty alt because the visible card title (a
 * sibling inside the same link) already supplies the link's accessible name —
 * a non-empty alt here would make screen readers announce the title twice. When
 * a post has no thumbnail we draw a CSS gradient placeholder with the post
 * title's first letter — no broken image, no blank box, no extra HTTP request.
 * The gradient hue is derived deterministically from the post ID so each
 * placeholder is stable and distinct.
 *
 * @param int    $post_id The related post ID.
 * @param string $title   The post title (used for the placeholder letter).
 * @return string Safe HTML for the card media area.
 */
function kindred_card_media( $post_id, $title ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$image = get_the_post_thumbnail(
			$post_id,
			'medium',
			array(
				'class'    => 'kindred-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => '', // Decorative: the visible title names the link.
			)
		);

		if ( $image ) {
			return '<span class="kindred-card__media">' . $image . '</span>';
		}
	}

	// Placeholder: deterministic hue from the ID so it's stable per post.
	$letter = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
	$letter = '' !== $letter ? $letter : '•'; // Bullet fallback for empty titles.
	$hue    = (int) $post_id * 47 % 360;

	return sprintf(
		'<span class="kindred-card__media kindred-card__media--placeholder" style="--kindred-hue:%1$d" aria-hidden="true"><span class="kindred-card__letter">%2$s</span></span>',
		(int) $hue,
		esc_html( $letter )
	);
}

/**
 * Build the full "You might also like" section markup.
 *
 * Returns an empty string when there are no related posts, so callers can safely
 * concatenate or echo the result without guarding. Every dynamic value is
 * escaped on output; the static SVG and class hooks are hand-authored.
 *
 * @param int $post_id The current post to render relatives for.
 * @return string Safe section HTML, or '' when nothing to show.
 */
function kindred_get_section_html( $post_id ) {
	$post_id = (int) $post_id;
	$ids     = kindred_get_related_ids( $post_id );

	if ( empty( $ids ) ) {
		return '';
	}

	$heading    = apply_filters( 'kindred_heading', __( 'You might also like', 'kindred-related' ) );
	$heading_id = 'kindred-heading-' . $post_id;

	$cards = '';
	foreach ( $ids as $id ) {
		$permalink = get_permalink( $id );
		if ( ! $permalink ) {
			continue;
		}

		$title = get_the_title( $id );
		$title = '' !== $title ? $title : __( '(untitled)', 'kindred-related' );
		$media = kindred_card_media( $id, $title );

		// Machine-readable date for assistive tech, human date for display.
		$date_iso   = get_the_date( 'c', $id );
		$date_human = get_the_date( '', $id );

		$cards .= sprintf(
			'<li class="kindred-card" data-kindred-card>' .
				'<a class="kindred-card__link" href="%1$s">' .
					'%2$s' .
					'<span class="kindred-card__body">' .
						'<span class="kindred-card__title">%3$s</span>' .
						'<time class="kindred-card__date" datetime="%4$s">%5$s</time>' .
					'</span>' .
				'</a>' .
			'</li>',
			esc_url( $permalink ),
			$media, // Built and escaped in kindred_card_media().
			esc_html( $title ),
			esc_attr( $date_iso ),
			esc_html( $date_human )
		);
	}

	if ( '' === $cards ) {
		return '';
	}

	return sprintf(
		'<section class="kindred" aria-labelledby="%1$s" data-kindred>' .
			'<h2 class="kindred__heading" id="%1$s">%2$s</h2>' .
			'<ul class="kindred__grid">%3$s</ul>' .
		'</section>',
		esc_attr( $heading_id ),
		esc_html( $heading ),
		$cards // Each card built and escaped above.
	);
}

/**
 * Echo the related-posts section. The manual placement helper for themes.
 *
 * Usage in a template: `if ( function_exists( 'kindred_related_posts' ) ) kindred_related_posts();`
 *
 * @param int|null $post_id Optional post ID; defaults to the current post.
 * @return void
 */
function kindred_related_posts( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	// Output is fully escaped inside kindred_get_section_html().
	echo kindred_get_section_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Shortcode handler: [kindred_related].
 *
 * Lets users drop the section into post content or a block editor shortcode
 * block. Returns markup (does not echo) as shortcodes must.
 *
 * @return string Section HTML for the current post, or '' off-context.
 */
function kindred_shortcode() {
	$post_id = (int) get_the_ID();

	return $post_id ? kindred_get_section_html( $post_id ) : '';
}
add_shortcode( 'kindred_related', 'kindred_shortcode' );

/**
 * Whether the current request is a single post where the section can appear.
 *
 * Centralizes the guard shared by the content filter and the asset enqueue so
 * they can never drift apart. Filterable so themes can opt views in or out
 * (e.g. to disable auto-append when placing the section manually).
 *
 * @return bool
 */
function kindred_is_active() {
	$active = ! is_admin() && ! is_feed() && is_singular( 'post' );

	/**
	 * Filter whether Kindred auto-appends and enqueues on this request.
	 *
	 * Returning false disables the automatic after-content section and the
	 * asset enqueue — useful when placing the section manually via the
	 * template tag or shortcode.
	 *
	 * @param bool $active Whether Kindred is active for this view.
	 */
	return (bool) apply_filters( 'kindred_is_active', $active );
}

/**
 * Append the related-posts section after single-post content.
 *
 * Guards on the main query in the loop for single posts only, so the section is
 * never injected into excerpts, archives, feeds, REST responses, or secondary
 * queries. Returns early (and unchanged) when there are no relatives.
 *
 * @param string $content The post content.
 * @return string
 */
function kindred_append_section( $content ) {
	if ( ! kindred_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	/**
	 * Filter whether to auto-append the section to the content.
	 *
	 * Set false to keep the assets but place the section manually with
	 * kindred_related_posts() or the [kindred_related] shortcode.
	 *
	 * @param bool $auto Whether to append automatically.
	 */
	if ( ! apply_filters( 'kindred_auto_append', true ) ) {
		return $content;
	}

	return $content . kindred_get_section_html( (int) get_the_ID() );
}
add_filter( 'the_content', 'kindred_append_section', 25 );

/**
 * Register and enqueue the section stylesheet and reveal script.
 *
 * Both ship as real, versioned asset files (assets/kindred-related.css and
 * assets/js/motion.js) rather than inline blobs, and load only on single posts
 * where the section can appear. The motion script is enqueued in the footer and
 * deferred (see kindred_defer_script) so it never blocks rendering — the section
 * is fully visible and usable without it.
 *
 * @return void
 */
function kindred_enqueue_assets() {
	if ( ! kindred_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'kindred-related',
		plugins_url( 'assets/kindred-related.css', __FILE__ ),
		array(),
		KINDRED_VERSION
	);

	wp_enqueue_script(
		'kindred-related-motion',
		plugins_url( 'assets/js/motion.js', __FILE__ ),
		array(),
		KINDRED_VERSION,
		true // Load in the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'kindred_enqueue_assets' );

/**
 * Add a `defer` attribute to the motion script tag.
 *
 * Keeps the enhancement non-render-blocking and supports WordPress back to 5.0
 * (the `strategy` enqueue arg only arrived in 6.3). A no-op for every handle but
 * this plugin's motion script.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function kindred_defer_script( $tag, $handle ) {
	if ( 'kindred-related-motion' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'kindred_defer_script', 10, 2 );

/**
 * Flip the document to its "JS available" state as early as possible.
 *
 * Prints a tiny, self-contained snippet in the <head> that swaps the
 * `kindred-no-js` class on <html> for `kindred-js`. This is the only inline
 * script and contains no animation logic (all of that lives in motion.js). The
 * reveal CSS is scoped to `.kindred-js`, so a no-JS visitor — or one whose
 * script fails — always sees the cards fully rendered (true progressive
 * enhancement, zero flash). The output is a fixed literal; nothing to escape.
 *
 * @return void
 */
function kindred_print_js_class() {
	if ( ! kindred_is_active() ) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('kindred-no-js');document.documentElement.classList.add('kindred-js');</script>\n";
}
add_action( 'wp_head', 'kindred_print_js_class', 1 );

/**
 * Seed the <html> element with a `kindred-no-js` class.
 *
 * The reveal CSS is scoped to `.kindred-js`, so this default guarantees no-JS
 * visitors keep the cards visible. The early head snippet promotes it to
 * `kindred-js` the instant scripting is confirmed available.
 *
 * The `language_attributes` filter passes the FULL attribute string for the
 * <html> tag (e.g. `lang="en-US" prefix="..."`), not a bare class list, so we
 * merge our class into an existing `class="..."` attribute when present and
 * append a new one otherwise — never emitting a stray boolean attribute.
 *
 * @param string $output Full attribute string for the <html> element.
 * @return string Attribute string including the `kindred-no-js` class.
 */
function kindred_html_no_js_class( $output ) {
	if ( ! kindred_is_active() ) {
		return $output;
	}

	// Already present (e.g. another hook ran first): leave as-is.
	if ( false !== strpos( $output, 'kindred-no-js' ) ) {
		return $output;
	}

	// Merge into an existing class="..." / class='...' attribute if there is one.
	$merged = preg_replace(
		'/\bclass=("|\')(.*?)\1/',
		'class=$1$2 kindred-no-js$1',
		$output,
		1,
		$replaced
	);

	if ( $replaced ) {
		return $merged;
	}

	// No class attribute yet: append one.
	$output = trim( $output );

	return '' === $output ? 'class="kindred-no-js"' : $output . ' class="kindred-no-js"';
}
add_filter( 'language_attributes', 'kindred_html_no_js_class' );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function kindred_load_textdomain() {
	load_plugin_textdomain( 'kindred-related', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'kindred_load_textdomain' );
