=== Contents — Smart Table of Contents ===
Contributors: wpaithemes
Tags: table of contents, toc, navigation, accessibility, anchor links
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically adds a tidy, accessible "Contents" box to long posts and pages. Smooth-scrolls, highlights the section you are reading, adapts to any theme. Zero config.

== Description ==

Contents builds a clean table of contents for any long post or page. When your
content has three or more headings, it slugs each heading and prepends a tidy
"Contents" navigation box that links to every section — no shortcodes, no setup,
no settings to wrestle with.

It is built to feel like a premium plugin while staying completely free and
self-contained: no external libraries, no CDNs, no web fonts, and no network
calls of any kind.

* **Theme-adaptive.** The box tints itself from the surrounding text color via
  `currentColor` and a few scoped `--contents-*` custom properties, so it looks
  right on light AND dark themes without fighting your design. Override any
  custom property to fully restyle it.
* **Reads where you are.** As you scroll, the link for the section currently in
  view is highlighted (with `aria-current="true"` and an `.is-active` class),
  driven by a hand-rolled `IntersectionObserver`.
* **Smooth, respectful motion.** Clicking a link smoothly scrolls to the section
  and moves focus to the heading so keyboard and screen-reader users land in the
  right place. Visitors who ask for reduced motion get an instant jump instead —
  honored by both a JavaScript gate and a CSS `@media` block.
* **Accessible.** A semantic `<nav aria-label="Table of contents">` wraps a
  nested list of anchor links. The collapse control is a real `<button>` with a
  correct `aria-expanded` state, fully keyboard operable, with visible focus.
* **Collapsible on mobile.** On small screens the list collapses behind a tap
  target so it never pushes your article down; it stays expanded on wider
  screens. The collapse animates a GPU-cheap grid track — zero layout shift.
* **Safe and tidy.** Existing heading ids are never clobbered, generated slugs
  are guaranteed unique within the document, empty headings are skipped, and the
  parse is memoized per post so calling `the_content` twice costs nothing extra.
* **Stays out of the way.** Bails on the admin, feeds, embeds, the REST API, and
  archives. Short posts (under the threshold) are left completely untouched.

Developers can tune it with filters:

`add_filter( 'contents_min_headings', fn() => 4 );           // require 4+ headings`
`add_filter( 'contents_heading_levels', fn() => array( 2 ) ); // h2 only`
`add_filter( 'contents_title', fn() => 'On this page' );       // rename the box`

Authors can disable the TOC on a single post by adding a custom field
`contents_toc_disable` set to `1` (or `yes` / `true` / `on`). Individual headings
can be excluded by adding a `contents-skip` class or a `data-contents-skip`
attribute to them.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose contents-toc.zip and click Install Now.
3. Click Activate. That's it — open any long post or page to see the Contents box.

There is nothing to configure. The box appears automatically on singular content
with three or more headings.

== Frequently Asked Questions ==

= Does it work on any theme? =

Yes. The box derives its colors from the current text color rather than hardcoded
values, so it adapts to light and dark themes automatically. Themes can override
the `--contents-*` custom properties to restyle it.

= Will it overwrite ids I already added to my headings? =

No. Existing, non-empty heading ids are always preserved and linked to. Only
headings without an id get a freshly generated, unique slug.

= How do I turn it off for one post? =

Add a custom field named `contents_toc_disable` with a value of `1` to that post.
You can also exclude a single heading by giving it a `contents-skip` class.

= Which headings are included? =

By default h2 and h3. Use the `contents_heading_levels` filter to change that
(for example, h2 only, or to add h4).

= Does it make network requests or add a settings page? =

No. It is zero-config and fully self-contained — no settings page, no external
requests, no third-party services, and no stored data beyond an optional
per-post custom field you set yourself.

== Screenshots ==

1. The Contents box above a post, with the active section highlighted, shown on
   both light and dark themes.

== Changelog ==

= 1.0.0 =
* Initial release.
* Automatic, accessible table of contents on singular posts/pages with three or
  more headings (h2/h3 by default; filterable).
* Stable, unique heading slugs that never clobber existing ids.
* Smooth scroll with focus management, honoring `prefers-reduced-motion`.
* Active-section highlighting via a hand-rolled `IntersectionObserver`
  (`aria-current` + `.is-active`).
* Collapsible on small screens via a native, keyboard-operable `<button>`.
* Theme-adaptive styling using `currentColor` and scoped custom properties; no
  external libraries, CDNs, fonts, or network calls.
* Per-post disable via the `contents_toc_disable` custom field, plus
  `contents_min_headings`, `contents_heading_levels`, `contents_title`,
  `contents_is_active`, and `contents_after_inject` filters.
