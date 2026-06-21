=== Cypher — Code Blocks ===
Contributors: wpaithemes
Tags: code, syntax, copy code, code block, line numbers
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Makes your <pre><code> blocks feel premium — copy button, language label, line numbers, tidy scroll, theme-adaptive styling. No highlighter library. Zero config.

== Description ==

Cypher quietly upgrades every code block on your posts and pages. It adds a
copy-to-clipboard button, an optional language label, optional line numbers,
clean horizontal scrolling for long lines, and theme-adaptive styling that looks
right on light AND dark themes — all without loading a single external highlighter
library, CDN, web font, or making any network call.

It is built to feel like a premium plugin while staying completely free and
self-contained: vanilla PHP, JavaScript, and CSS, with inline SVG icons. There is
nothing to configure — activate it and your code blocks are instantly better.

* **Copy to clipboard.** Each block gets a real `<button>` that copies the exact
  source, flips to a "Copied!" confirmation, and announces the result to screen
  readers via an `aria-live` region. Uses the async Clipboard API with a resilient
  `execCommand` fallback. The button is hidden until JavaScript confirms it can
  work, so visitors without JavaScript never see a dead control.
* **Language label.** Reads the language from the code element's `language-*` /
  `lang-*` class (or a `data-lang` attribute) — the convention used by the block
  editor, Markdown, and popular highlighters — and shows a tidy label like
  "JavaScript" or "PHP". No language present? Nothing is shown.
* **Line numbers.** Accurate, right-aligned ordinals in a reserved gutter, drawn
  with a pure-CSS counter. The gutter width is reserved up front, so numbers never
  cause a layout shift, and the code text is never altered — copying still yields
  the original source exactly.
* **Tidy horizontal scroll.** Long lines scroll inside the block, never the page,
  with a slim theme-tinted scrollbar.
* **Theme-adaptive.** Colors derive from the surrounding text color via
  `currentColor` and a few scoped `--cypher-*` custom properties, so blocks look
  right on light AND dark themes without fighting your design. Override any custom
  property to fully restyle a block.
* **Accessible & respectful.** Semantic markup, keyboard-operable button with a
  visible focus ring, an `aria-live` status announcement, and motion that honors
  `prefers-reduced-motion` (both a JavaScript gate and a CSS `@media` block).
* **Loads only where needed.** Assets are enqueued only on singular posts/pages
  that actually contain a code block — pages with no code pay nothing.
* **Safe and tidy.** The original `<pre><code>` is preserved verbatim (whitespace,
  entities, and any existing highlighting survive untouched), the transform is
  idempotent, and a pathological block falls back to the unmodified content rather
  than ever blanking a post.

Developers can tune it with filters:

`add_filter( 'cypher_show_copy', '__return_false' );          // hide the copy button`
`add_filter( 'cypher_show_line_numbers', '__return_false' );  // turn off line numbers`
`add_filter( 'cypher_show_language', '__return_false' );      // hide the language label`
`add_filter( 'cypher_language_label', fn( $label ) => $label ); // remap a label`

Individual blocks can be excluded by adding a `cypher-skip` class or a
`data-cypher-skip` attribute to the `<pre>` element.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose cypher-code.zip and click Install Now.
3. Click Activate. That's it — open any post or page with a code block to see it.

There is nothing to configure. The enhancements appear automatically on singular
content that contains a `<pre><code>` block.

== Frequently Asked Questions ==

= Does it highlight syntax with colors? =

No — and that is intentional. Cypher deliberately ships no highlighter library,
CDN, or web font, so it stays fast, private, and self-contained. It makes your
existing code blocks premium (copy button, language label, line numbers, tidy
scroll, adaptive styling) without the weight of a highlighter. If your theme or
another plugin already adds token colors, Cypher preserves that markup untouched.

= Does it work on any theme? =

Yes. Blocks derive their colors from the current text color rather than hardcoded
values, so they adapt to light and dark themes automatically. Themes can override
the `--cypher-*` custom properties to restyle them.

= Where does the language label come from? =

From the code element's `language-*` or `lang-*` class, or a `data-lang`
attribute. The block editor's "Code" block plus a small CSS class, fenced
Markdown, and most highlighters all set one of these. If none is present, no
label is shown.

= Will copying include the line numbers? =

No. Line numbers are drawn by CSS in a reserved gutter and are never part of the
copied text. Copying always yields the exact original source.

= How do I turn off a feature, or skip one block? =

Use the `cypher_show_copy`, `cypher_show_line_numbers`, or `cypher_show_language`
filters to toggle a feature site-wide. To skip a single block, add a `cypher-skip`
class or a `data-cypher-skip` attribute to its `<pre>` element.

= Does it make network requests or add a settings page? =

No. It is zero-config and fully self-contained — no settings page, no external
requests, no third-party services, and no stored data.

== Screenshots ==

1. A code block enhanced by Cypher — language label, copy button, and line
   numbers — shown on both light and dark themes.

== Changelog ==

= 1.0.0 =
* Initial release.
* Copy-to-clipboard button on every code block, with a "Copied!" confirmation and
  an `aria-live` announcement; async Clipboard API with an `execCommand` fallback.
* Optional language label read from the code element's `language-*` / `lang-*`
  class or `data-lang` attribute, with a clean known-language display map.
* Optional, accurate line numbers via a pure-CSS counter in a reserved (no-shift)
  gutter; the code text is never altered.
* Tidy horizontal scroll for long lines, with a theme-tinted slim scrollbar.
* Theme-adaptive styling using `currentColor` and scoped custom properties; no
  external highlighter, CDNs, fonts, or network calls; inline SVG icons.
* Accessible: semantic markup, keyboard-operable button, visible focus, aria-live
  status, and `prefers-reduced-motion` honored by a JS gate and a CSS `@media`.
* Assets enqueued only on singular content that contains a code block.
* `cypher_show_copy`, `cypher_show_line_numbers`, `cypher_show_language`,
  `cypher_language_label`, `cypher_is_active`, and `cypher_post_has_code` filters,
  plus per-block opt-out via a `cypher-skip` class or `data-cypher-skip`.
