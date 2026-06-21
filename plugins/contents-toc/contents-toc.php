<?php
/**
 * Plugin Name: Contents — Smart Table of Contents
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Automatically adds a tidy, accessible "Contents" navigation box to long posts and pages. Smooth-scrolls to sections, highlights the section you are reading, and adapts to any theme. Zero configuration.
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: contents-toc
 *
 * @package ContentsToc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const CONTENTS_VERSION = '1.0.0';

/**
 * Minimum number of qualifying headings before a table of contents is shown.
 *
 * Tunable via the `contents_min_headings` filter. Below this threshold a short
 * post does not warrant a TOC, so the plugin stays out of the way entirely.
 */
const CONTENTS_MIN_HEADINGS = 3;

/**
 * Resolve the minimum-headings threshold, honoring the filter.
 *
 * Falls back to the default if a filter returns something below 1, so a
 * misbehaving filter can never produce a nonsensical (or zero) threshold.
 *
 * @return int Minimum headings, always >= 1.
 */
function contents_min_headings() {
	$min = (int) apply_filters( 'contents_min_headings', CONTENTS_MIN_HEADINGS );

	return $min > 0 ? $min : CONTENTS_MIN_HEADINGS;
}

/**
 * Which heading levels are collected into the table of contents.
 *
 * Defaults to h2 and h3, which is the right depth for almost every article.
 * Filterable so a site can include h4 or restrict to h2 only. Values are
 * normalized to a unique, ordered list of integers in the 1–6 range.
 *
 * @return int[] Heading levels, e.g. array( 2, 3 ).
 */
function contents_heading_levels() {
	$levels = apply_filters( 'contents_heading_levels', array( 2, 3 ) );

	if ( ! is_array( $levels ) ) {
		$levels = array( 2, 3 );
	}

	$levels = array_values( array_unique( array_filter( array_map( 'intval', $levels ), 'contents_is_heading_level' ) ) );
	sort( $levels );

	return empty( $levels ) ? array( 2, 3 ) : $levels;
}

/**
 * Whether an integer is a valid HTML heading level (1–6).
 *
 * @param int $level Candidate level.
 * @return bool
 */
function contents_is_heading_level( $level ) {
	return $level >= 1 && $level <= 6;
}

/**
 * Whether the current request is a singular view that may carry a TOC.
 *
 * Centralizes the guard shared by the content filter and the asset enqueues so
 * they can never drift apart. Bails on the admin, feeds, embeds, the REST API,
 * and archives — anywhere a per-document table of contents makes no sense.
 *
 * @return bool
 */
function contents_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& is_singular();

	/**
	 * Filter whether the table of contents may load on this request.
	 *
	 * @param bool $active Whether the TOC is active for the current view.
	 */
	return (bool) apply_filters( 'contents_is_active', $active );
}

/**
 * Whether the table of contents is disabled for a specific post.
 *
 * Authors can switch the TOC off per post with a `contents_toc_disable` custom
 * field set to a truthy value (1, true, yes, on). Filterable so a site can wire
 * the toggle to its own meta key or logic.
 *
 * @param int $post_id Post ID being rendered.
 * @return bool True if the TOC should be suppressed for this post.
 */
function contents_is_disabled_for_post( $post_id ) {
	$disabled = false;

	$flag = get_post_meta( $post_id, 'contents_toc_disable', true );

	if ( '' !== $flag ) {
		$disabled = in_array( strtolower( (string) $flag ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Filter whether the TOC is disabled for this post.
	 *
	 * @param bool $disabled Whether the TOC is suppressed.
	 * @param int  $post_id  The post ID.
	 */
	return (bool) apply_filters( 'contents_is_disabled_for_post', $disabled, $post_id );
}

/**
 * Convert heading text into a stable, unique slug for use as an id.
 *
 * Uses WordPress's own sanitize_title so slugs match permalink conventions and
 * support non-Latin scripts. Uniqueness is enforced against ids already present
 * in the document (existing or freshly generated) by appending -2, -3, ….
 *
 * @param string $text Heading text (may contain inline HTML).
 * @param array  $used Map of ids already taken (id => true). Modified by ref.
 * @return string A slug that is unique within $used.
 */
function contents_unique_slug( $text, &$used ) {
	$base = sanitize_title( wp_strip_all_tags( $text ) );

	if ( '' === $base ) {
		$base = 'section';
	}

	$slug = $base;
	$n    = 2;

	while ( isset( $used[ $slug ] ) ) {
		$slug = $base . '-' . $n;
		$n++;
	}

	$used[ $slug ] = true;

	return $slug;
}

/**
 * Parse content for headings, assigning ids, and return the heading map.
 *
 * Walks every <h{level}> in the content for the configured levels. Each heading
 * gets a stable id: an existing, non-empty id is preserved (never clobbered);
 * otherwise a slug derived from the heading text is injected. The returned
 * content has those ids in place, and the returned items describe the TOC.
 *
 * This is intentionally a single regex pass over already-rendered content (no
 * DOM extension dependency), and it skips headings explicitly marked to be
 * excluded with a `data-contents-skip` attribute or a `.contents-skip` class.
 *
 * @param string $content The rendered post content.
 * @return array {
 *     @type string $content The content with ids ensured on each heading.
 *     @type array  $items   Ordered list of array( level, id, text ) entries.
 * }
 */
function contents_collect_headings( $content ) {
	$levels = contents_heading_levels();
	$items  = array();
	$used   = array();

	// First, register every id already present so generated slugs never collide
	// with hand-authored anchors elsewhere in the content.
	if ( preg_match_all( '/\sid=("|\')(.*?)\1/i', $content, $existing ) ) {
		foreach ( $existing[2] as $existing_id ) {
			$existing_id = trim( $existing_id );
			if ( '' !== $existing_id ) {
				$used[ $existing_id ] = true;
			}
		}
	}

	$level_pattern = implode( '', $levels ); // e.g. "23" for h2/h3.
	$pattern       = '/<h([' . $level_pattern . '])\b([^>]*)>(.*?)<\/h\1>/is';

	$new_content = preg_replace_callback(
		$pattern,
		function ( $m ) use ( &$items, &$used ) {
			$level = (int) $m[1];
			$attrs = $m[2];
			$inner = $m[3];

			// Allow opting a single heading out of the TOC.
			if (
				false !== stripos( $attrs, 'data-contents-skip' )
				|| preg_match( '/class=("|\')[^"\']*\bcontents-skip\b[^"\']*\1/i', $attrs )
			) {
				return $m[0];
			}

			$text = trim( wp_strip_all_tags( $inner ) );

			// Skip empty headings (e.g. a stray <h2></h2>); nothing to link to.
			if ( '' === $text ) {
				return $m[0];
			}

			// Preserve an existing, non-empty id; otherwise inject a fresh slug.
			if ( preg_match( '/\sid=("|\')(.*?)\1/i', $attrs, $id_match ) && '' !== trim( $id_match[2] ) ) {
				$id = trim( $id_match[2] );
			} else {
				$id        = contents_unique_slug( $text, $used );
				$new_attrs = $attrs . ' id="' . esc_attr( $id ) . '"';

				$m[0] = '<h' . $level . $new_attrs . '>' . $inner . '</h' . $level . '>';
			}

			$items[] = array(
				'level' => $level,
				'id'    => $id,
				'text'  => $text,
			);

			return $m[0];
		},
		$content
	);

	// preg_replace_callback returns null on failure (e.g. catastrophic regex):
	// fall back to the untouched content so we never blank a post.
	if ( null === $new_content ) {
		return array(
			'content' => $content,
			'items'   => array(),
		);
	}

	return array(
		'content' => $new_content,
		'items'   => $items,
	);
}

/**
 * Build the table-of-contents navigation markup from collected headings.
 *
 * Renders a semantic <nav> labelled for assistive technology, a heading, a
 * native <button> toggle (used only on small screens / when JS is present), and
 * a nested unordered list of anchor links. Every dynamic value is escaped on
 * output. The list preserves heading depth by nesting deeper levels.
 *
 * @param array $items Ordered list of array( level, id, text ) entries.
 * @return string Safe HTML for the TOC, or '' if there is nothing to show.
 */
function contents_build_toc_html( $items ) {
	if ( empty( $items ) ) {
		return '';
	}

	$title = apply_filters( 'contents_title', __( 'Contents', 'contents-toc' ) );

	// Normalize levels so the shallowest present level becomes depth 0. This
	// keeps nesting correct even when a post starts at h3 or skips h2.
	$present = array();
	foreach ( $items as $item ) {
		$present[ $item['level'] ] = true;
	}
	$present_levels = array_keys( $present );
	sort( $present_levels );

	$depth_of = array();
	foreach ( $present_levels as $i => $level ) {
		$depth_of[ $level ] = $i;
	}

	$list      = '';
	$cur_depth = 0; // Depth of the item currently awaiting its closing </li>.

	foreach ( $items as $index => $item ) {
		$depth = $depth_of[ $item['level'] ];

		if ( 0 === $index ) {
			// First item establishes the baseline depth; no list nesting yet.
			$cur_depth = $depth;
		} elseif ( $depth > $cur_depth ) {
			// Descend: open one nested <ul> per depth step, keeping the parent
			// <li> open. We always step exactly one depth at a time so every <ul>
			// is matched by exactly one </ul> on the way back up — even when a
			// heading jump (e.g. h2 straight to h4) skips a level, the markup
			// stays perfectly balanced and valid.
			$steps = $depth - $cur_depth;
			for ( $s = 0; $s < $steps; $s++ ) {
				$list .= '<ul class="contents-toc__sublist">';
			}
			$cur_depth = $depth;
		} else {
			// Same depth: close the previous sibling <li>.
			$list .= '</li>';

			if ( $depth < $cur_depth ) {
				// Shallower: climb back up, closing one nested <ul> and the <li>
				// that contained it for each depth step we ascend. Mirrors the
				// per-step descent above so opens and closes stay balanced.
				$steps = $cur_depth - $depth;
				for ( $s = 0; $s < $steps; $s++ ) {
					$list .= '</ul></li>';
				}
				$cur_depth = $depth;
			}
		}

		$list .= sprintf(
			'<li class="contents-toc__item"><a class="contents-toc__link" href="#%1$s" data-contents-target="%1$s">%2$s</a>',
			esc_attr( $item['id'] ),
			esc_html( $item['text'] )
		);
	}

	// Close the final item, then unwind every nested list still open back to the
	// root, closing each wrapper <li> as we go. ($cur_depth is the number of
	// nested <ul> levels currently open.)
	$list .= '</li>';
	for ( $s = 0; $s < $cur_depth; $s++ ) {
		$list .= '</ul></li>';
	}

	$toggle = sprintf(
		'<button type="button" class="contents-toc__toggle" aria-expanded="true" aria-controls="contents-toc-list">'
			. '<span class="contents-toc__title">%1$s</span>'
			. '<svg class="contents-toc__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="6 9 12 15 18 9"></polyline></svg>'
			. '</button>',
		esc_html( $title )
	);

	// The list is the grid container for the small-screen collapse animation; its
	// single child, .contents-toc__inner, holds the items so the whole region can
	// interpolate from 1fr to 0fr cleanly. On wide screens the grid wrapper is a
	// no-op and the list renders normally.
	return sprintf(
		'<nav class="contents-toc" aria-label="%1$s" data-contents-toc>%2$s'
			. '<ul id="contents-toc-list" class="contents-toc__list"><li class="contents-toc__inner">'
			. '<ul class="contents-toc__items">%3$s</ul>'
			. '</li></ul></nav>',
		esc_attr__( 'Table of contents', 'contents-toc' ),
		$toggle, // Static, trusted markup assembled above.
		$list    // Built from escaped, trusted parts above.
	);
}

/**
 * Inject the table of contents into singular post/page content.
 *
 * Guards on the main query in the loop for singular views only, so the TOC is
 * never added to excerpts, archives, feeds, REST responses, or secondary
 * queries. Headings are slugged in place, then the TOC box is prepended.
 *
 * Expensive work (the regex parse) is memoized per post for the duration of the
 * request so a theme calling the_content twice does not pay for it twice.
 *
 * @param string $content The post content.
 * @return string
 */
function contents_prepend_toc( $content ) {
	if ( ! contents_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = get_the_ID();

	if ( ! $post_id || contents_is_disabled_for_post( $post_id ) ) {
		return $content;
	}

	static $cache = array();

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$parsed = contents_collect_headings( $content );

	if ( count( $parsed['items'] ) < contents_min_headings() ) {
		// Not enough headings to warrant a TOC; return the original content
		// untouched (no injected ids either — nothing referenced them).
		$cache[ $post_id ] = $content;
		return $content;
	}

	$toc    = contents_build_toc_html( $parsed['items'] );
	$result = $toc . $parsed['content'];

	/**
	 * Filter the final content after the TOC has been prepended.
	 *
	 * @param string $result  Content with the TOC prepended and ids ensured.
	 * @param string $toc      The TOC markup alone.
	 * @param array  $items    The collected heading items.
	 * @param int    $post_id  The post ID.
	 */
	$result = apply_filters( 'contents_after_inject', $result, $toc, $parsed['items'], $post_id );

	$cache[ $post_id ] = $result;

	return $result;
}
// Priority 12 runs after the core content filters that turn block markup and
// shortcodes into real HTML headings (`do_blocks` at 9, `wpautop`/`wptexturize`
// at 10), so we always parse rendered <h2>/<h3> tags rather than raw block
// comments — while still landing before most theme/plugin additions.
add_filter( 'the_content', 'contents_prepend_toc', 12 );

/**
 * Register and enqueue the front-end stylesheet and behavior script.
 *
 * Both ship as real, versioned files in /assets (no inline blobs) and load only
 * on singular views where a TOC can appear. The script is enqueued in the footer
 * and deferred (see contents_defer_script) so it never blocks rendering: the TOC
 * links work as plain anchors before it runs.
 *
 * @return void
 */
function contents_enqueue_assets() {
	if ( ! contents_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'contents-toc',
		plugins_url( 'assets/contents-toc.css', __FILE__ ),
		array(),
		CONTENTS_VERSION
	);

	wp_enqueue_script(
		'contents-toc',
		plugins_url( 'assets/js/contents.js', __FILE__ ),
		array(),
		CONTENTS_VERSION,
		true // In the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'contents_enqueue_assets' );

/**
 * Add the `defer` attribute to the plugin's footer script tag.
 *
 * Keeps support back to WordPress 5.0 (the `strategy` enqueue argument arrived
 * in 6.3). The script is pure progressive enhancement, so deferring it is safe.
 *
 * @param string $tag    The full <script> tag for the enqueued handle.
 * @param string $handle The script's registered handle.
 * @return string The (possibly) modified script tag.
 */
function contents_defer_script( $tag, $handle ) {
	if ( 'contents-toc' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'contents_defer_script', 10, 2 );

/**
 * Seed the <html> element with a `contents-no-js` class.
 *
 * The entrance-reveal CSS is scoped to `.contents-js`, so this default
 * guarantees that visitors without JavaScript (or whose script fails) always see
 * the Contents box fully rendered. The early head snippet below promotes it to
 * `contents-js` the instant scripting is confirmed available, before first
 * paint, so the reveal never flashes the box on then off.
 *
 * @param string $classes Space-separated class list from `language_attributes`.
 * @return string
 */
function contents_html_no_js_class( $classes ) {
	if ( ! contents_is_active() ) {
		return $classes;
	}

	$classes = trim( $classes );

	return '' === $classes ? 'contents-no-js' : $classes . ' contents-no-js';
}
add_filter( 'language_attributes', 'contents_html_no_js_class' );

/**
 * Flip the document to its "JS available" state as early as possible.
 *
 * Prints a tiny, self-contained snippet in the <head> that swaps the
 * `contents-no-js` class on <html> for `contents-js`. This is the only inline
 * script the plugin emits and it contains no behavior logic — all of that lives
 * in the enqueued contents.js. Running it in <head> means the CSS that primes
 * the box for its entrance only ever applies when JS is present, so there is no
 * flash for no-JS visitors and no stuck-hidden box.
 *
 * The output is a fixed string literal, so there is nothing dynamic to escape.
 *
 * @return void
 */
function contents_print_js_class() {
	if ( ! contents_is_active() ) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('contents-no-js');document.documentElement.classList.add('contents-js');</script>\n";
}
add_action( 'wp_head', 'contents_print_js_class', 1 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function contents_load_textdomain() {
	load_plugin_textdomain( 'contents-toc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'contents_load_textdomain' );
