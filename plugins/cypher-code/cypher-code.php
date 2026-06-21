<?php
/**
 * Plugin Name: Cypher — Code Blocks
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Makes <pre><code> code blocks feel premium — a copy-to-clipboard button, an optional language label, optional line numbers, tidy horizontal scroll, and clean theme-adaptive styling that looks right on light and dark themes. No external highlighter, no CDNs, no fonts, no network calls. Zero configuration.
 * Category:   Content & Reading
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cypher-code
 *
 * @package Cypher
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const CYPHER_VERSION = '1.0.0';

/**
 * Whether the copy-to-clipboard button is rendered on code blocks.
 *
 * Tunable via the `cypher_show_copy` filter. The button is pure progressive
 * enhancement (it is hidden until JavaScript wires it up), so disabling it
 * simply removes the affordance entirely.
 *
 * @return bool
 */
function cypher_show_copy() {
	return (bool) apply_filters( 'cypher_show_copy', true );
}

/**
 * Whether line numbers are rendered alongside code blocks.
 *
 * Tunable via the `cypher_show_line_numbers` filter. Line numbers are drawn by
 * CSS counters on a wrapper class, so toggling this only changes whether the
 * class (and its reserved gutter) is emitted — there is never a layout shift
 * because the gutter width is fixed when present.
 *
 * @return bool
 */
function cypher_show_line_numbers() {
	return (bool) apply_filters( 'cypher_show_line_numbers', true );
}

/**
 * Whether the language label is rendered when a language is detected.
 *
 * Tunable via the `cypher_show_language` filter. The label is read from the
 * code element's `language-*` / `lang-*` class (or a `data-lang` attribute); if
 * no language is present nothing is shown regardless of this setting.
 *
 * @return bool
 */
function cypher_show_language() {
	return (bool) apply_filters( 'cypher_show_language', true );
}

/**
 * Whether the current request is a singular view that may carry code blocks.
 *
 * Centralizes the guard shared by the content filter and the asset enqueues so
 * they can never drift apart. Bails on the admin, feeds, embeds, the REST API,
 * and archives — anywhere per-document code-block enhancement makes no sense.
 *
 * @return bool
 */
function cypher_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& is_singular();

	/**
	 * Filter whether Cypher may enhance code blocks on this request.
	 *
	 * @param bool $active Whether Cypher is active for the current view.
	 */
	return (bool) apply_filters( 'cypher_is_active', $active );
}

/**
 * Whether the active theme opts in to native WPAI companion placement.
 *
 * When a theme declares `add_theme_support( 'wpai-companions' )` it promises to
 * fire `wpai_entry_top` / `wpai_entry_bottom` action hooks around the article
 * body. Cypher enhances code blocks INLINE in `the_content` regardless, so this
 * is used only to decide where (if anywhere) a one-line, theme-width "assets
 * ready" sentinel is emitted — but in practice Cypher's work is entirely inside
 * the content, so the companion path simply confirms the integration is present
 * and leaves the inline transform as the single source of truth. Kept so the
 * dual-mode contract matches the sibling plugins and a theme can rely on it.
 *
 * @return bool True when the theme supports the companion hooks.
 */
function cypher_theme_supports_companions() {
	return (bool) current_theme_supports( 'wpai-companions' );
}

/**
 * Detect a human-friendly language label from a code element's attributes.
 *
 * Looks at (in order) a `data-lang` attribute, then any `language-*` or `lang-*`
 * class token — the conventions used by the block editor, Markdown converters,
 * and popular highlighters. The raw token is normalized to a tidy display label
 * via a small known-language map, falling back to an uppercased token so even
 * unknown languages read cleanly. Returns '' when no language is present.
 *
 * @param string $attrs The raw attribute string from the <code ...> tag.
 * @return string A display label (e.g. "JavaScript"), or '' if none found.
 */
function cypher_detect_language( $attrs ) {
	$token = '';

	// Prefer an explicit data-lang attribute.
	if ( preg_match( '/\bdata-lang(?:uage)?=("|\')\s*([^"\']+?)\s*\1/i', $attrs, $m ) ) {
		$token = $m[2];
	}

	// Otherwise look for a language-*/lang-* class token.
	if ( '' === $token && preg_match( '/class=("|\')([^"\']*)\1/i', $attrs, $cm ) ) {
		if ( preg_match( '/\b(?:language|lang|brush:)\s*-?\s*([A-Za-z0-9#+.\-]+)/i', $cm[2], $lm ) ) {
			$token = $lm[1];
		}
	}

	$token = trim( strtolower( $token ) );

	if ( '' === $token || 'none' === $token || 'plain' === $token || 'text' === $token ) {
		return '';
	}

	$labels = array(
		'js'         => 'JavaScript',
		'javascript' => 'JavaScript',
		'jsx'        => 'JSX',
		'ts'         => 'TypeScript',
		'typescript' => 'TypeScript',
		'tsx'        => 'TSX',
		'html'       => 'HTML',
		'markup'     => 'HTML',
		'xml'        => 'XML',
		'css'        => 'CSS',
		'scss'       => 'SCSS',
		'sass'       => 'Sass',
		'less'       => 'Less',
		'php'        => 'PHP',
		'py'         => 'Python',
		'python'     => 'Python',
		'rb'         => 'Ruby',
		'ruby'       => 'Ruby',
		'go'         => 'Go',
		'golang'     => 'Go',
		'rs'         => 'Rust',
		'rust'       => 'Rust',
		'java'       => 'Java',
		'kt'         => 'Kotlin',
		'kotlin'     => 'Kotlin',
		'swift'      => 'Swift',
		'c'          => 'C',
		'cpp'        => 'C++',
		'cs'         => 'C#',
		'csharp'     => 'C#',
		'sh'         => 'Shell',
		'bash'       => 'Bash',
		'shell'      => 'Shell',
		'zsh'        => 'Zsh',
		'ps'         => 'PowerShell',
		'powershell' => 'PowerShell',
		'sql'        => 'SQL',
		'json'       => 'JSON',
		'yaml'       => 'YAML',
		'yml'        => 'YAML',
		'toml'       => 'TOML',
		'ini'        => 'INI',
		'md'         => 'Markdown',
		'markdown'   => 'Markdown',
		'diff'       => 'Diff',
		'docker'     => 'Dockerfile',
		'dockerfile' => 'Dockerfile',
		'graphql'    => 'GraphQL',
		'gql'        => 'GraphQL',
		'http'       => 'HTTP',
		'nginx'      => 'Nginx',
		'apache'     => 'Apache',
	);

	if ( isset( $labels[ $token ] ) ) {
		$label = $labels[ $token ];
	} else {
		// Unknown token: present it cleanly (strip separators, uppercase short
		// acronyms, title-case longer words) without inventing a wrong name.
		$clean = preg_replace( '/[^A-Za-z0-9#+.]/', '', $token );
		$label = ( strlen( $clean ) <= 3 ) ? strtoupper( $clean ) : ucfirst( $clean );
	}

	/**
	 * Filter the resolved language display label for a code block.
	 *
	 * @param string $label The display label.
	 * @param string $token The normalized language token detected from markup.
	 */
	return (string) apply_filters( 'cypher_language_label', $label, $token );
}

/**
 * Wrap every <pre><code> block in the content with Cypher's enhancement shell.
 *
 * A single regex pass over already-rendered content (no DOM extension needed):
 * each `<pre ...><code ...>...</code></pre>` is wrapped in a figure that carries
 * the language label, the (JS-activated) copy button, and the line-number class.
 * The original <pre> and its inner code are preserved verbatim — we never touch
 * the code text itself, so whitespace, entities, and any pre-existing
 * highlighting markup survive untouched.
 *
 * Blocks already wrapped by Cypher (idempotency on double `the_content`) and
 * blocks explicitly opted out with a `cypher-skip` class or `data-cypher-skip`
 * attribute on the <pre> are left exactly as they are.
 *
 * @param string $content The rendered post content.
 * @return string The content with code blocks wrapped, or unchanged on failure.
 */
function cypher_wrap_code_blocks( $content ) {
	// Cheap pre-check: skip the regex entirely when there is nothing to do.
	if ( false === stripos( $content, '<pre' ) || false === stripos( $content, '<code' ) ) {
		return $content;
	}

	$show_copy     = cypher_show_copy();
	$show_lang     = cypher_show_language();
	$show_numbers  = cypher_show_line_numbers();

	$pattern = '/<pre\b([^>]*)>\s*(<code\b([^>]*)>.*?<\/code>)\s*<\/pre>/is';

	$wrapped = preg_replace_callback(
		$pattern,
		function ( $m ) use ( $show_copy, $show_lang, $show_numbers ) {
			$pre_attrs  = $m[1];
			$code_block = $m[2];
			$code_attrs = $m[3];

			// Idempotency: if we (or anything) already wrapped this, leave it.
			// Our wrapper is the only thing that adds the marker class, and the
			// regex matches the inner <pre> so a re-run would otherwise re-wrap.
			if (
				false !== stripos( $pre_attrs, 'data-cypher-skip' )
				|| preg_match( '/class=("|\')[^"\']*\b(?:cypher-skip|cypher-pre)\b[^"\']*\1/i', $pre_attrs )
			) {
				return $m[0];
			}

			$language = $show_lang ? cypher_detect_language( $code_attrs ) : '';

			// Reconstruct the <pre> with our marker class added (so a second pass
			// is a no-op) while preserving every original attribute.
			$pre_open = '<pre' . cypher_add_class( $pre_attrs, 'cypher-pre' ) . '>';

			$figure_classes = 'cypher-block';
			if ( $show_numbers ) {
				$figure_classes .= ' cypher-block--numbered';
			}
			if ( '' !== $language ) {
				$figure_classes .= ' cypher-block--has-lang';
			}

			$bar = cypher_build_bar( $language, $show_copy );

			return sprintf(
				'<figure class="%1$s" data-cypher>%2$s<div class="cypher-block__scroll">%3$s%4$s</div></figure>',
				esc_attr( $figure_classes ),
				$bar,                        // Trusted, escaped in cypher_build_bar().
				$pre_open,                   // Reconstructed from a safe class merge.
				$code_block                  // Original inner code, preserved verbatim.
			);
		},
		$content
	);

	// preg_replace_callback returns null on failure (e.g. backtrack limit hit on
	// a pathological block): fall back to the untouched content so we never blank
	// a post.
	if ( null === $wrapped ) {
		return $content;
	}

	return $wrapped;
}

/**
 * Merge a class token into an existing attribute string safely.
 *
 * If the attributes already declare a class list, the token is appended inside
 * it; otherwise a fresh class attribute is added. The token is escaped for use
 * in an attribute value. Used to stamp our marker class onto the original <pre>
 * without disturbing its other attributes.
 *
 * @param string $attrs The raw attribute string (may be empty).
 * @param string $class The single class token to add.
 * @return string The attribute string with the class merged in.
 */
function cypher_add_class( $attrs, $class ) {
	$class = esc_attr( $class );

	if ( preg_match( '/(\sclass=)("|\')(.*?)\2/i', $attrs, $m ) ) {
		$merged = trim( $m[3] . ' ' . $class );

		return str_replace( $m[0], $m[1] . $m[2] . $merged . $m[2], $attrs );
	}

	return $attrs . ' class="' . $class . '"';
}

/**
 * Build the top bar of a code block: language label + copy button.
 *
 * Output is assembled entirely from escaped, trusted parts. The copy button is
 * a real <button> (keyboard operable, visible focus) and is hidden via CSS until
 * the behavior script marks the document JS-ready, so it never appears as a dead
 * control for no-JS visitors. An adjacent aria-live region announces the copy
 * result to assistive technology. Returns '' when there is nothing to show.
 *
 * @param string $language  Display language label, or '' for none.
 * @param bool   $show_copy Whether to include the copy button.
 * @return string Safe HTML for the bar, or '' if empty.
 */
function cypher_build_bar( $language, $show_copy ) {
	$has_lang = ( '' !== $language );

	if ( ! $has_lang && ! $show_copy ) {
		return '';
	}

	$lang_html = '';
	if ( $has_lang ) {
		$lang_html = sprintf(
			'<span class="cypher-block__lang">%s</span>',
			esc_html( $language )
		);
	}

	$copy_html = '';
	if ( $show_copy ) {
		// Inline SVG icons (no font/icon library). Two icons: a clipboard (idle)
		// and a check (confirmed); CSS swaps them by the button's data-copied
		// state set by the script. The visible label text is for sighted users;
		// aria-label keeps the accessible name stable as the icon changes.
		$copy_icon = '<svg class="cypher-block__icon cypher-block__icon--copy" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
		$done_icon = '<svg class="cypher-block__icon cypher-block__icon--done" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="20 6 9 17 4 12"></polyline></svg>';

		$copy_html = sprintf(
			'<button type="button" class="cypher-block__copy" data-cypher-copy aria-label="%1$s">%2$s%3$s<span class="cypher-block__copy-label" data-cypher-copy-label data-label-idle="%4$s" data-label-done="%5$s">%4$s</span></button>'
				. '<span class="cypher-block__status" role="status" aria-live="polite"></span>',
			esc_attr__( 'Copy code to clipboard', 'cypher-code' ),
			$copy_icon,
			$done_icon,
			esc_attr__( 'Copy', 'cypher-code' ),
			esc_attr__( 'Copied!', 'cypher-code' )
		);
	}

	return sprintf(
		'<div class="cypher-block__bar">%1$s<span class="cypher-block__bar-spacer"></span>%2$s</div>',
		$lang_html,
		$copy_html
	);
}

/**
 * Enhance code blocks on singular content in the main loop.
 *
 * Guards on the main query in the loop for singular views only, so excerpts,
 * archives, feeds, REST responses, and secondary queries are untouched. The
 * transform is idempotent, so a theme calling `the_content` twice is harmless.
 *
 * @param string $content The post content.
 * @return string
 */
function cypher_filter_content( $content ) {
	if ( ! cypher_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	return cypher_wrap_code_blocks( $content );
}
// Priority 12 runs after the core content filters that turn block markup into
// real HTML (`do_blocks` at 9, `wpautop`/`wptexturize` at 10), so we always
// wrap rendered <pre><code> tags rather than raw block comments — while still
// landing before most theme/plugin additions.
add_filter( 'the_content', 'cypher_filter_content', 12 );

/**
 * Register and enqueue the front-end stylesheet and behavior script.
 *
 * Both ship as real, versioned files in /assets (no inline blobs). They load
 * only on singular views, and only when the post content actually contains a
 * code block — so a page with no code pays nothing. The script is deferred (see
 * cypher_defer_script) and is pure progressive enhancement: code blocks render
 * and scroll fine before it runs; it only adds the copy interaction.
 *
 * @return void
 */
function cypher_enqueue_assets() {
	if ( ! cypher_is_active() ) {
		return;
	}

	if ( ! cypher_current_post_has_code() ) {
		return;
	}

	wp_enqueue_style(
		'cypher-code',
		plugins_url( 'assets/cypher-code.css', __FILE__ ),
		array(),
		CYPHER_VERSION
	);

	wp_enqueue_script(
		'cypher-code',
		plugins_url( 'assets/js/cypher.js', __FILE__ ),
		array(),
		CYPHER_VERSION,
		true // In the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'cypher_enqueue_assets' );

/**
 * Whether the current main-query singular post contains a code block.
 *
 * Cheap, cached check used to gate asset loading: we look at the raw post
 * content for a `<pre` followed by a `<code` (the editor's code block, fenced
 * Markdown output, and hand-written code all render to this). The result is
 * memoized for the request so the enqueue gate and any other caller share one
 * lookup. We deliberately read the stored content rather than running the full
 * `the_content` pipeline here, keeping the gate fast on every page load.
 *
 * @return bool
 */
function cypher_current_post_has_code() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$cache = false;

	$post = get_post();
	if ( ! $post ) {
		return $cache;
	}

	$raw = (string) $post->post_content;

	// Match an opening <pre ...> with a <code somewhere after it. This errs
	// toward loading assets when code is present; a false positive only enqueues
	// two tiny files, while a false negative would leave a block unstyled.
	$cache = (bool) preg_match( '/<pre\b[^>]*>\s*(?:<[^>]+>\s*)*<code\b/i', $raw )
		|| ( false !== stripos( $raw, 'wp-block-code' ) )
		|| ( false !== stripos( $raw, '<pre' ) && false !== stripos( $raw, '<code' ) );

	/**
	 * Filter whether the current post is treated as containing a code block.
	 *
	 * @param bool    $has  Whether a code block was detected.
	 * @param WP_Post $post The current post object.
	 */
	$cache = (bool) apply_filters( 'cypher_post_has_code', $cache, $post );

	return $cache;
}

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
function cypher_defer_script( $tag, $handle ) {
	if ( 'cypher-code' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'cypher_defer_script', 10, 2 );

/**
 * Seed the <html> element with a `cypher-no-js` class.
 *
 * The copy button and other JS-only affordances are revealed only under
 * `.cypher-js`, so this default guarantees that visitors without JavaScript (or
 * whose script fails) never see a dead copy button. The early head snippet below
 * promotes it to `cypher-js` the instant scripting is confirmed available,
 * before first paint, so the button never flashes in then out.
 *
 * The `language_attributes` filter receives the FULL attribute string for the
 * <html> tag (e.g. `lang="en-US" dir="ltr"`), not a bare class list — so we must
 * merge our token into a real `class` attribute (creating one if absent) rather
 * than appending a stray token, which would emit invalid markup.
 *
 * @param string $attributes The <html> attribute string from `language_attributes`.
 * @return string
 */
function cypher_html_no_js_class( $attributes ) {
	if ( ! cypher_is_active() || ! cypher_current_post_has_code() ) {
		return $attributes;
	}

	// Merge into an existing class attribute when present, otherwise add one.
	if ( preg_match( '/(\sclass=)("|\')(.*?)\2/i', ' ' . $attributes, $m ) ) {
		$merged  = trim( $m[3] . ' cypher-no-js' );
		$replace = $m[1] . $m[2] . $merged . $m[2];

		return trim( str_replace( $m[0], $replace, ' ' . $attributes ) );
	}

	$attributes = trim( $attributes );

	return '' === $attributes ? 'class="cypher-no-js"' : $attributes . ' class="cypher-no-js"';
}
add_filter( 'language_attributes', 'cypher_html_no_js_class' );

/**
 * Flip the document to its "JS available" state as early as possible.
 *
 * Prints a tiny, self-contained snippet in the <head> that swaps the
 * `cypher-no-js` class on <html> for `cypher-js`. This is the only inline script
 * the plugin emits and it contains no behavior logic — all of that lives in the
 * enqueued cypher.js. Running it in <head> means the copy button only appears
 * when JS is present, so there is no flash for no-JS visitors.
 *
 * The output is a fixed string literal, so there is nothing dynamic to escape.
 *
 * @return void
 */
function cypher_print_js_class() {
	if ( ! cypher_is_active() || ! cypher_current_post_has_code() ) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('cypher-no-js');document.documentElement.classList.add('cypher-js');</script>\n";
}
add_action( 'wp_head', 'cypher_print_js_class', 1 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function cypher_load_textdomain() {
	load_plugin_textdomain( 'cypher-code', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'cypher_load_textdomain' );
