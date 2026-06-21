<?php
/**
 * Plugin Name: Oracle — FAQ & Schema
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Turn a plain list of questions and answers into an accessible FAQ accordion AND auto-emit a single valid FAQPage JSON-LD block — great for search engines and AI agents. Drop in the [oracle_faq] shortcode and write h3/h4 questions; the answers follow. Theme-adaptive, keyboard operable, reduced-motion-safe, zero configuration.
 * Category:   SEO & AI
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oracle-faq
 *
 * @package OracleFaq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const ORACLE_VERSION = '1.0.0';

/**
 * Heading levels (h-tags) that may mark a question inside the shortcode body.
 *
 * Defaults to h3 and h4 — the natural depth for FAQ questions nested under a
 * section heading. Filterable so a site can use h2/h3 instead. Values are
 * normalized to a unique, ordered list of integers in the 1–6 range.
 *
 * @return int[] Heading levels, e.g. array( 3, 4 ).
 */
function oracle_question_levels() {
	$levels = apply_filters( 'oracle_question_levels', array( 3, 4 ) );

	if ( ! is_array( $levels ) ) {
		$levels = array( 3, 4 );
	}

	$levels = array_values(
		array_unique(
			array_filter(
				array_map( 'intval', $levels ),
				static function ( $level ) {
					return $level >= 1 && $level <= 6;
				}
			)
		)
	);
	sort( $levels );

	return empty( $levels ) ? array( 3, 4 ) : $levels;
}

/**
 * The per-request store of Q/A pairs collected from every [oracle_faq] on the page.
 *
 * Each rendered accordion appends its pairs here so a single, deduplicated
 * FAQPage JSON-LD block can be emitted once in the footer — even when a page
 * contains several FAQ shortcodes. Acts as a static singleton via reference.
 *
 * @return array Reference to the collected pairs: list of array( q, a ).
 */
function &oracle_collected_pairs() {
	static $pairs = array();

	return $pairs;
}

/**
 * Record a question/answer pair for the page-level JSON-LD, de-duplicated by question.
 *
 * The answer is stored as plain text (tags stripped, whitespace collapsed) so
 * the schema carries clean, machine-readable text regardless of the markup the
 * author used in the visible accordion.
 *
 * @param string $question Plain-text question.
 * @param string $answer   Plain-text answer.
 * @return void
 */
function oracle_record_pair( $question, $answer ) {
	$question = trim( $question );
	$answer   = trim( $answer );

	if ( '' === $question || '' === $answer ) {
		return;
	}

	$pairs =& oracle_collected_pairs();

	// De-duplicate by question text so repeated questions don't bloat the schema.
	foreach ( $pairs as $existing ) {
		if ( $existing['q'] === $question ) {
			return;
		}
	}

	$pairs[] = array(
		'q' => $question,
		'a' => $answer,
	);
}

/**
 * Collapse runs of whitespace in a string to single spaces and trim.
 *
 * @param string $text Raw text.
 * @return string Normalized text.
 */
function oracle_normalize_text( $text ) {
	$text = preg_replace( '/\s+/u', ' ', (string) $text );

	return null === $text ? '' : trim( $text );
}

/**
 * Parse the shortcode body into an ordered list of question/answer items.
 *
 * Convention: a heading at one of the configured question levels (h3/h4 by
 * default) starts a new question; everything up to the next qualifying heading
 * (or the end) is that question's answer. Content before the first heading is
 * ignored, so an intro paragraph above the questions is harmless.
 *
 * This is a single, defensive regex split over already-rendered HTML — no DOM
 * extension dependency. Both the visible (HTML) answer and a plain-text answer
 * are returned so the accordion and the JSON-LD stay perfectly in sync.
 *
 * @param string $html The shortcode body, already run through the_content filters.
 * @return array[] Ordered list of array( question, answer_html, answer_text ).
 */
function oracle_parse_items( $html ) {
	$levels        = oracle_question_levels();
	$level_pattern = implode( '', $levels ); // e.g. "34" for h3/h4.

	// Split the body on every qualifying heading, keeping the heading chunk so we
	// can read its text. PREG_SPLIT_DELIM_CAPTURE puts the captured groups (the
	// full heading and its inner text) into the result between the surrounding
	// segments. The inner text is captured non-greedily across newlines.
	$pattern = '/(<h[' . $level_pattern . ']\b[^>]*>(.*?)<\/h[' . $level_pattern . ']>)/is';

	$parts = preg_split( $pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( false === $parts || empty( $parts ) ) {
		return array();
	}

	$items = array();

	// The split yields: [pre-heading text], [heading html], [heading inner], [answer],
	// [heading html], [heading inner], [answer], … We walk it three at a time after
	// the initial pre-heading segment.
	$count = count( $parts );

	// Skip the very first segment: anything before the first question heading.
	for ( $i = 1; $i < $count; $i += 3 ) {
		$heading_inner = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';
		$answer_html   = isset( $parts[ $i + 2 ] ) ? $parts[ $i + 2 ] : '';

		$question = oracle_normalize_text( wp_strip_all_tags( $heading_inner ) );

		if ( '' === $question ) {
			continue; // Empty heading — nothing to ask.
		}

		$answer_html = trim( $answer_html );
		$answer_text = oracle_normalize_text( wp_strip_all_tags( $answer_html ) );

		if ( '' === $answer_text ) {
			continue; // A question with no answer is not a valid FAQ entry.
		}

		$items[] = array(
			'question'    => $question,
			'answer_html' => $answer_html,
			'answer_text' => $answer_text,
		);
	}

	return $items;
}

/**
 * Build the accessible accordion markup for a set of parsed FAQ items.
 *
 * Each item is a question button (a real <button> with aria-expanded and
 * aria-controls) followed by a region panel (role-implied via the labelled
 * relationship) holding the answer HTML. The whole group is wrapped in a
 * <section aria-label="…"> so assistive technology announces it as an FAQ.
 *
 * Every dynamic value is escaped on output: question text via esc_html, the
 * answer HTML via wp_kses_post (post-grade markup only), and all ids/attributes
 * via esc_attr. The SVG chevron is a fixed, hand-authored literal.
 *
 * @param array[] $items     Parsed items: array( question, answer_html, answer_text ).
 * @param string  $unique_id A unique base for element ids within the document.
 * @return string Safe accordion HTML, or '' when there is nothing to render.
 */
function oracle_build_accordion_html( $items, $unique_id ) {
	if ( empty( $items ) ) {
		return '';
	}

	$label = apply_filters( 'oracle_section_label', __( 'Frequently asked questions', 'oracle-faq' ) );

	$rows = '';
	$n    = 0;

	foreach ( $items as $item ) {
		$n++;
		$button_id = $unique_id . '-q-' . $n;
		$panel_id  = $unique_id . '-a-' . $n;

		$rows .= sprintf(
			'<div class="oracle-faq__item" data-oracle-item>' .
				'<h3 class="oracle-faq__question">' .
					'<button type="button" class="oracle-faq__trigger" id="%1$s" aria-expanded="false" aria-controls="%2$s" data-oracle-trigger>' .
						'<span class="oracle-faq__label">%3$s</span>' .
						'<svg class="oracle-faq__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="6 9 12 15 18 9"></polyline></svg>' .
					'</button>' .
				'</h3>' .
				'<div class="oracle-faq__panel" id="%2$s" role="region" aria-labelledby="%1$s" data-oracle-panel hidden>' .
					'<div class="oracle-faq__answer">%4$s</div>' .
				'</div>' .
			'</div>',
			esc_attr( $button_id ),
			esc_attr( $panel_id ),
			esc_html( $item['question'] ),
			wp_kses_post( $item['answer_html'] )
		);
	}

	return sprintf(
		'<section class="oracle-faq" aria-label="%1$s" data-oracle-faq>%2$s</section>',
		esc_attr( $label ),
		$rows // Built from escaped, trusted parts above.
	);
}

/**
 * Shortcode handler: [oracle_faq] … h3/h4 questions with answers … [/oracle_faq].
 *
 * Renders the enclosed Q/A list as an accessible accordion and records each pair
 * for the page's single FAQPage JSON-LD block (emitted in the footer). Self-
 * closing or empty usage renders nothing. The body is passed through the_content
 * filters first so block markup, shortcodes, and wpautop produce real HTML — the
 * same parsing surface the rest of WordPress sees.
 *
 * @param array       $atts    Shortcode attributes (currently unused, reserved).
 * @param string|null $content The enclosed content (the Q/A list).
 * @return string Accordion HTML, or '' when there is nothing to render.
 */
function oracle_shortcode( $atts, $content = null ) {
	unset( $atts ); // Reserved for future options; keeps the signature stable.

	if ( null === $content || '' === trim( (string) $content ) ) {
		return '';
	}

	// Render the enclosed body to real HTML before we parse it: do_shortcode()
	// resolves any nested shortcodes (the outer [oracle_faq] tag is already
	// stripped by the shortcode API, so this does not recurse into itself), and
	// wpautop() wraps bare lines in <p> so answers parse as proper markup — the
	// same surface the rest of WordPress sees.
	$rendered = do_shortcode( $content );
	$rendered = wpautop( $rendered );

	$items = oracle_parse_items( $rendered );

	if ( empty( $items ) ) {
		return '';
	}

	// Record every pair for the page-level schema (deduped by question).
	foreach ( $items as $item ) {
		oracle_record_pair( $item['question'], $item['answer_text'] );
	}

	// A stable-enough unique base for ids: post id + a per-render counter so
	// multiple shortcodes on one page never collide.
	static $instance = 0;
	++$instance;
	$unique_id = 'oracle-faq-' . (int) get_the_ID() . '-' . $instance;

	return oracle_build_accordion_html( $items, $unique_id );
}
add_shortcode( 'oracle_faq', 'oracle_shortcode' );

/**
 * Print the single FAQPage JSON-LD block for the page, if any pairs were collected.
 *
 * Runs late in the footer so it captures every [oracle_faq] rendered anywhere on
 * the page (content, widgets, template parts). Emitting in the footer rather than
 * the head is required because the shortcodes render during the_content, which
 * runs after wp_head — collecting first and printing last guarantees the schema
 * contains the page's full, final question set.
 *
 * The document is built as a native PHP array and encoded with wp_json_encode.
 * Slashes are deliberately left escaped (no JSON_UNESCAPED_SLASHES) so a value
 * containing "</script>" cannot break out of the surrounding inline <script> tag —
 * the encoder writes "<\/script>", which is harmless. The surrounding tag is a
 * fixed, trusted literal.
 *
 * @return void
 */
function oracle_print_jsonld() {
	if ( is_admin() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$pairs =& oracle_collected_pairs();

	if ( empty( $pairs ) ) {
		return;
	}

	$entities = array();

	foreach ( $pairs as $pair ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $pair['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $pair['a'],
			),
		);
	}

	$document = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);

	/**
	 * Filter the assembled FAQPage JSON-LD document before output.
	 *
	 * @param array $document The schema.org FAQPage document.
	 * @param array $pairs    The collected question/answer pairs.
	 */
	$document = apply_filters( 'oracle_jsonld_document', $document, $pairs );

	// Keep slashes escaped so a "</script>" inside any answer becomes "<\/script>"
	// and cannot terminate the inline <script> tag below. JSON_UNESCAPED_UNICODE
	// keeps non-ASCII text readable; slash-escaping is the safety-critical part.
	$json = wp_json_encode( $document, JSON_UNESCAPED_UNICODE );

	if ( false === $json ) {
		return;
	}

	echo '<script type="application/ld+json">' . $json . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode escapes for inline embedding.
}
add_action( 'wp_footer', 'oracle_print_jsonld', 99 );

/**
 * Whether the current request is a front-end view where the accordion can appear.
 *
 * Centralizes the guard used by the asset enqueue. The shortcode itself is only
 * processed when present, so this just keeps assets off the admin, feeds, and the
 * REST API. Filterable so a site can opt views in or out.
 *
 * @return bool
 */
function oracle_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& is_singular();

	/**
	 * Filter whether Oracle's assets may load on this request.
	 *
	 * @param bool $active Whether the FAQ assets are active for this view.
	 */
	return (bool) apply_filters( 'oracle_is_active', $active );
}

/**
 * Register and enqueue the front-end stylesheet and behavior script.
 *
 * Both ship as real, versioned files in /assets (no inline blobs) and load only
 * on singular views where a shortcode can appear. The script is enqueued in the
 * footer and deferred (see oracle_defer_script) so it never blocks rendering: the
 * accordion is fully readable as plain expanded sections before it runs.
 *
 * @return void
 */
function oracle_enqueue_assets() {
	if ( ! oracle_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'oracle-faq',
		plugins_url( 'assets/oracle-faq.css', __FILE__ ),
		array(),
		ORACLE_VERSION
	);

	wp_enqueue_script(
		'oracle-faq',
		plugins_url( 'assets/js/oracle-faq.js', __FILE__ ),
		array(),
		ORACLE_VERSION,
		true // In the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'oracle_enqueue_assets' );

/**
 * Add the `defer` attribute to the plugin's footer script tag.
 *
 * Keeps support back to WordPress 5.0 (the `strategy` enqueue argument arrived
 * in 6.3). The script is pure progressive enhancement, so deferring it is safe —
 * without it the panels are simply shown expanded.
 *
 * @param string $tag    The full <script> tag for the enqueued handle.
 * @param string $handle The script's registered handle.
 * @return string The (possibly) modified script tag.
 */
function oracle_defer_script( $tag, $handle ) {
	if ( 'oracle-faq' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'oracle_defer_script', 10, 2 );

/**
 * Seed the <html> element with an `oracle-no-js` class.
 *
 * The collapse-by-default CSS is scoped to `.oracle-js`, so this default
 * guarantees that visitors without JavaScript (or whose script fails) always see
 * every answer fully expanded and readable. The early head snippet below promotes
 * it to `oracle-js` the instant scripting is confirmed available, before first
 * paint, so the panels start collapsed only when the toggle can actually open
 * them — no stuck-hidden content.
 *
 * The `language_attributes` filter passes the FULL attribute string for the
 * <html> tag, so we merge our class into an existing `class="…"` attribute when
 * present and append a new one otherwise — never emitting a stray attribute.
 *
 * @param string $output Full attribute string for the <html> element.
 * @return string Attribute string including the `oracle-no-js` class.
 */
function oracle_html_no_js_class( $output ) {
	if ( ! oracle_is_active() ) {
		return $output;
	}

	if ( false !== strpos( $output, 'oracle-no-js' ) ) {
		return $output;
	}

	$merged = preg_replace(
		'/\bclass=("|\')(.*?)\1/',
		'class=$1$2 oracle-no-js$1',
		$output,
		1,
		$replaced
	);

	if ( $replaced ) {
		return $merged;
	}

	$output = trim( $output );

	return '' === $output ? 'class="oracle-no-js"' : $output . ' class="oracle-no-js"';
}
add_filter( 'language_attributes', 'oracle_html_no_js_class' );

/**
 * Flip the document to its "JS available" state as early as possible.
 *
 * Prints a tiny, self-contained snippet in the <head> that swaps the
 * `oracle-no-js` class on <html> for `oracle-js`. This is the only inline script
 * the plugin emits and it contains no behavior logic — all of that lives in the
 * enqueued oracle-faq.js. Running it in <head> means the CSS that collapses the
 * panels only applies when JS is present to expand them again, so no-JS visitors
 * read every answer and there is no flash.
 *
 * The output is a fixed string literal, so there is nothing dynamic to escape.
 *
 * @return void
 */
function oracle_print_js_class() {
	if ( ! oracle_is_active() ) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('oracle-no-js');document.documentElement.classList.add('oracle-js');</script>\n";
}
add_action( 'wp_head', 'oracle_print_js_class', 1 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function oracle_load_textdomain() {
	load_plugin_textdomain( 'oracle-faq', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'oracle_load_textdomain' );
