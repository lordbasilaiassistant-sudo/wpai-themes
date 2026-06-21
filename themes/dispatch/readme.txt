=== Dispatch ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: news, magazine, blog, two-columns, right-sidebar, custom-menu, custom-logo, featured-images, threaded-comments, translation-ready, editor-style, block-styles, light, accessibility-ready

A fast, modern digital-news theme — a breaking bar, a live headline ticker, a tight news grid with a hero lead, and a category color-coding system.

== Description ==

Dispatch is a sharp, modern WordPress theme for a digital newsroom. The front
page opens with a thin breaking bar and a live headline ticker, then a tight
news grid: one large hero lead story beside a column of secondary stories,
followed by a two-up river of the rest. Headlines are set in condensed, bold
sans; everything reads in crisp system fonts that load instantly.

Its signature device is a category color-coding system — every section gets its
own stable color that runs through the category tag, the thin rail on each
cover, and the placeholder tint — so readers can scan the page by topic at a
glance. Long-form articles read on a comfortable measure with clear headings,
strong pull-quotes, and styled code, tables, and captions.

It ships with real CSS — custom properties, a tight type scale, a responsive
two-column reading layout, and accessible focus states — and uses only
system fonts, so there are no external requests.

Features:

* Modern news grid: a hero lead story beside a column of secondary stories,
  then a two-up card river of the rest
* Thin top breaking bar with the current date, and a live headline ticker of
  the latest stories (a static, scrollable link list without JS)
* Category color-coding: each section gets its own consistent color across
  tags, cover rails, and image-less placeholders
* Condensed bold headlines, crisp system sans body, and tabular meta
* Featured images on the grid, single, and pages — with a graceful
  category-tinted placeholder when a post has none, so the grid never breaks
* Two-column layout with a sticky, widgetised sidebar (Search, Recent Posts,
  Recent Comments, Archives, Categories all styled)
* Threaded comments and a polished comment form
* Accessible, responsive mobile navigation with a no-JS fallback
* Skip link, visible focus-visible states, semantic landmarks, AA contrast,
  and full prefers-reduced-motion support
* Block editor color palette and font sizes via theme.json
* Customizer color controls (Accent, Background, Bar) with instant live preview
* Native companion-plugin integration via wpai_entry_top / wpai_entry_bottom
* Custom logo, primary + footer menus, and a styled 404
* Translation-ready (text domain: dispatch). No external fonts, no tracking.

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose dispatch.zip and click Install Now.
3. Click Activate.
4. Set a Primary menu under Appearance > Menus, add widgets under
   Appearance > Widgets, assign categories to your posts (for the color-coding),
   and give your posts featured images for the best look.

== Changelog ==

= 1.0.0 =
* Initial release.
* Modern digital-news layout: breaking bar, live headline ticker, a news grid
  with a hero lead beside secondary stories, and a two-up card river.
* Category color-coding system across tags, cover rails, and placeholders.
* Signature motion: a live ticker marquee that pauses on hover/focus, and
  staggered news-grid slide-up reveals on scroll — fully gated behind a `js`
  class and prefers-reduced-motion, with everything visible without JS.
* Customizer color controls (Accent, Background, Bar) with live preview.
* Native companion-plugin placement via the wpai_entry_top / wpai_entry_bottom
  action hooks, with a matching native skin for the badge, Contents box, and
  related-posts block.
* Accessible, responsive, system-font-only, and translation-ready.
