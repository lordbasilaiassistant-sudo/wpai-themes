=== Oracle — FAQ & Schema ===
Contributors: wpaithemes
Tags: faq, accordion, schema, json-ld, structured data
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn a plain list of questions and answers into an accessible FAQ accordion AND auto-emit a valid FAQPage JSON-LD block. Great for search and AI agents. Zero config.

== Description ==

Oracle turns the questions and answers you already write into two things at once:
a polished, accessible FAQ accordion your readers can use, and a single valid
**FAQPage JSON-LD** block that search engines and AI agents read to understand
your page. You write the content once; Oracle handles the markup and the schema.

Drop the `[oracle_faq]` shortcode into any post or page and write your questions
as `h3` (or `h4`) headings, with each answer in the content that follows:

`[oracle_faq]`
`### Do you ship internationally?`
`Yes — we ship to over 60 countries with tracked delivery.`
`### How long does delivery take?`
`Most orders arrive within 3–5 business days.`
`[/oracle_faq]`

That is the entire setup. Each heading becomes a collapsible question; everything
up to the next heading becomes its answer. Oracle renders an accessible accordion
and, on that page, prints one clean FAQPage JSON-LD script containing every Q/A
pair — properly escaped — so it is eligible for rich results and easy for AI
agents to parse.

It is built to feel like a premium plugin while staying completely free and
self-contained: no external libraries, no CDNs, no web fonts, and no network
calls of any kind.

* **Two outputs, one source.** The visible accordion and the JSON-LD are built
  from the same parsed questions and answers, so they can never drift out of
  sync. The schema carries clean plain-text answers; the accordion keeps your
  rich markup.
* **Valid FAQPage schema.** A single `application/ld+json` block per page with
  `@type: FAQPage` and a `mainEntity` array of `Question` / `acceptedAnswer`
  nodes, encoded with `wp_json_encode` so every value is safely escaped. Multiple
  shortcodes on one page are merged into one block and de-duplicated by question.
* **Accessible by construction.** Each question is a real `<button>` inside a
  heading, with correct `aria-expanded` and `aria-controls`, paired to an answer
  region labelled by the button. Full keyboard support: Enter/Space toggle, and
  Up/Down/Home/End move between questions (the WAI-ARIA accordion pattern).
* **Theme-adaptive.** The accordion tints itself from the surrounding text color
  via `currentColor` and a few scoped `--oracle-*` custom properties, so it looks
  right on light AND dark themes without fighting your design. Override any custom
  property to fully restyle it.
* **Respectful, zero-shift motion.** Panels expand with a GPU-cheap grid-rows
  animation and the chevron rotates with a transform — no width/height animation,
  so there is no cumulative layout shift. `prefers-reduced-motion` is honored by
  both a JavaScript gate and a CSS `@media` block.
* **Works without JavaScript.** Until the script takes control, every answer is
  shown fully expanded and readable. If JS is blocked or fails, nothing is ever
  stuck hidden.
* **Safe and tidy.** Output is escaped (`esc_html`, `esc_attr`, `wp_kses_post`),
  the body is parsed defensively without any DOM extension dependency, and the
  schema is emitted once in the footer so it captures every FAQ on the page.

Developers can tune it with filters:

`add_filter( 'oracle_question_levels', fn() => array( 2, 3 ) ); // use h2/h3 as questions`
`add_filter( 'oracle_section_label', fn() => 'Common questions' ); // accordion aria-label`
`add_filter( 'oracle_jsonld_document', fn( $doc ) => $doc );       // tweak the schema`

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose oracle-faq.zip and click Install Now.
3. Click Activate.
4. Edit any post or page, add the `[oracle_faq]` shortcode, and write your
   questions as `h3` headings with the answers below them.

There is nothing to configure. The accordion appears where you place the
shortcode, and the FAQPage JSON-LD is added to that page automatically.

== Frequently Asked Questions ==

= How do I mark a question versus an answer? =

Inside the `[oracle_faq]` shortcode, write each question as an `h3` (or `h4`)
heading. Everything from that heading up to the next heading becomes its answer.
Use the `oracle_question_levels` filter to switch to `h2`/`h3` if you prefer.

= Does it work on any theme? =

Yes. The accordion derives its colors from the current text color rather than
hardcoded values, so it adapts to light and dark themes automatically. Themes can
override the `--oracle-*` custom properties to restyle it.

= Will the JSON-LD be valid? =

Yes. Oracle emits one `FAQPage` document per page with a `mainEntity` array of
`Question` nodes, each with an `acceptedAnswer`. It is encoded with
`wp_json_encode`, which escapes the payload for safe inline embedding. You can
verify it with Google's Rich Results Test or any schema validator.

= What if I put more than one FAQ shortcode on a page? =

All of their questions are merged into a single FAQPage block and de-duplicated
by question text, so a page never emits two competing FAQ schemas.

= What happens if JavaScript is disabled? =

Every answer is shown fully expanded and readable. The collapse/expand behavior
is pure progressive enhancement layered on top of working, visible content.

= Does it make network requests or add a settings page? =

No. It is zero-config and fully self-contained — no settings page, no external
requests, no third-party services, and no stored data.

== Screenshots ==

1. An Oracle FAQ accordion with one question open, plus a peek at the generated
   FAQPage JSON-LD — shown on both light and dark themes.

== Changelog ==

= 1.0.0 =
* Initial release.
* `[oracle_faq]` shortcode turning h3/h4 questions (filterable) and their answers
  into an accessible, theme-adaptive accordion.
* Single, valid FAQPage JSON-LD per page, merged and de-duplicated across
  multiple shortcodes and emitted in the footer (escaped via wp_json_encode).
* Real `<button>` triggers with aria-expanded / aria-controls, region panels, and
  full WAI-ARIA keyboard support (Enter/Space, Up/Down/Home/End).
* Theme-adaptive styling using `currentColor` and scoped custom properties; no
  external libraries, CDNs, fonts, or network calls.
* Reduced-motion-safe, zero-layout-shift expand/collapse (grid-rows + transform),
  gated by both JS and a CSS @media block.
* Works fully without JavaScript (answers shown expanded).
* `oracle_question_levels`, `oracle_section_label`, `oracle_is_active`, and
  `oracle_jsonld_document` filters.
