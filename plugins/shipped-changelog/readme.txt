=== Shipped — Auto Changelog & Roadmap ===
Contributors: wpaithemes
Tags: changelog, roadmap, timeline, automation, accessibility
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A self-building changelog timeline and roadmap status board — automated product management with zero config. Theme-adaptive, accessible, cached.

== Description ==

Shipped turns the posts you already write into a polished, self-maintaining
**changelog timeline** and a **roadmap status board** — no settings page, no data
entry, no external services. Publish a post in your "Changelog" or "Roadmap"
category and it appears automatically; edit it and the display updates. That's the
whole workflow.

Drop either shortcode on any page:

`[shipped_changelog]` — an accessible vertical timeline of releases, newest first.
`[shipped_roadmap]` — a Planned / In progress / Shipped board.

= How it builds itself =

* **Changelog timeline.** Every published post in your **Changelog** category
  becomes a timeline entry using its date, title, and excerpt (or a trimmed bit of
  content). If a post is tagged with a version like `v1.2.0`, that version shows as
  a chip on the entry. Newest releases sit at the top.
* **Roadmap board.** Every published post in your **Roadmap** category is sorted
  into a column by a status tag/term — tag a post `planned`, `in-progress`, or
  `shipped` (common aliases like `wip`, `done`, and `released` work too) and it
  lands in the right column. Untagged items default to Planned, so nothing is lost.
* **Zero config.** Just create the categories and start posting. Don't want to use
  posts? Type entries straight into the shortcode (see below) — both sources merge.

= Premium where it counts =

* **Theme-adaptive.** Surfaces, borders, timeline rail, markers, and muted text all
  derive from your theme's text color via `currentColor` and `color-mix`, so it
  looks right on light and dark themes out of the box. Override any `--shipped-*`
  custom property to restyle it.
* **Responsive.** The roadmap board sits three columns across on desktop and stacks
  to a single column on mobile, with no fixed breakpoints to fight your layout.
* **Accessible.** Labelled regions (`aria-labelledby`), real semantic lists, real
  links, machine-readable `datetime` on dates, visible keyboard focus, and a fully
  honored `prefers-reduced-motion` — reduced-motion visitors get everything with no
  animation at all.
* **Crafted entrance.** As the timeline and board scroll into view, entries fade and
  slide up in a gentle stagger. The motion animates only `transform` and `opacity`
  (no layout shift) and is pure progressive enhancement — with JavaScript off, or if
  it fails, everything is simply visible from the start.
* **Fast.** Entry IDs for each section are sourced with a tuned `WP_Query` (IDs only,
  `no_found_rows`, no meta priming) and cached in a transient. The cache is rebuilt
  automatically the instant you save or delete a post, or edit a term — so pages stay
  snappy without ever showing stale entries.
* **Optional structured data.** The changelog can emit a compact schema.org
  `ItemList` JSON-LD block so search engines and AI agents can read your releases.
  On by default for the changelog; toggle with `schema="false"`.
* **Self-contained.** No external libraries, CDNs, web fonts, network calls, or
  tracking. The scroll reveal is hand-rolled with vanilla `IntersectionObserver`.

= Manual entries (optional) =

If you'd rather not use posts, type entries straight into the shortcode, one per
line, fields separated by a pipe. Manual and post-sourced entries merge.

Changelog — `date | version | title | description`:

`[shipped_changelog]`
`2024-06-01 | v1.2.0 | Faster search | Rebuilt the index for instant results.`
`2024-05-10 | v1.1.0 | Dark mode | Automatic, theme-aware dark mode.`
`[/shipped_changelog]`

Roadmap — `status | title | description`:

`[shipped_roadmap]`
`shipped | Dark mode | Now live for everyone.`
`in-progress | Realtime sync | Building the websocket layer.`
`planned | Mobile app | On the horizon.`
`[/shipped_roadmap]`

= Template tags =

Place either section directly in a theme template:

`<?php if ( function_exists( 'shipped_changelog' ) ) { shipped_changelog(); } ?>`
`<?php if ( function_exists( 'shipped_roadmap' ) ) { shipped_roadmap(); } ?>`

= Developer filters =

`add_filter( 'shipped_changelog_category', fn() => 'Releases' );` — source the
timeline from a differently named category.
`add_filter( 'shipped_roadmap_category', fn() => 'Plans' );` — change the roadmap
category.
`add_filter( 'shipped_roadmap_statuses', $cb );` — rename columns or add accepted
status slugs.
`add_filter( 'shipped_max_entries', fn() => 50 );` — cap how many entries are pulled.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose shipped-changelog.zip and click Install Now.
3. Click Activate.
4. Create a category named **Changelog** and/or **Roadmap** and publish a post or
   two in it. For the roadmap, tag posts `planned`, `in-progress`, or `shipped`.
5. Add `[shipped_changelog]` or `[shipped_roadmap]` to any page. Done.

== Frequently Asked Questions ==

= Do I have to configure anything? =

No. Create a "Changelog" and/or "Roadmap" category, publish posts in it, and drop
the shortcode. The timeline and board build and update themselves.

= How do I show a version number on a changelog entry? =

Tag the post with a version like `v1.2.0` (or `1.2.0`). Shipped detects the first
version-shaped tag and shows it as a chip on that entry.

= How does the roadmap decide which column an item goes in? =

By a status tag or term on the post: `planned`, `in-progress`, or `shipped`. Common
aliases work too (`backlog`, `wip`, `doing`, `done`, `released`, and more). Items
with no recognized status default to the Planned column.

= Can I use my own category names? =

Yes. Use the `shipped_changelog_category` and `shipped_roadmap_category` filters, or
pass `category="My Category"` on the shortcode.

= Does it work on dark themes? =

Yes. All surfaces and text tint themselves from your theme's current text color
rather than hardcoded grays, so the timeline and board adapt to light and dark
themes automatically.

= Does it slow down my site or make network requests? =

No. Section entry IDs are cached in a transient and recomputed only when you save or
delete a post (or edit a term). The CSS and reveal script load only on pages that
actually contain a shortcode. There are no external requests and no settings page.

== Changelog ==

= 1.0.0 =
* Initial release.
* `[shipped_changelog]`: accessible vertical release timeline sourced from a
  "Changelog" category (date, title, excerpt; version chip from a `vX.Y.Z` tag),
  newest first.
* `[shipped_roadmap]`: accessible Planned / In progress / Shipped status board
  sourced from a "Roadmap" category, bucketed by a status tag/term.
* Optional manual entries via a simple pipe-delimited inner-content convention;
  merges with post-sourced entries.
* Template tags `shipped_changelog()` and `shipped_roadmap()`.
* Theme-adaptive (currentColor / color-mix, light & dark), responsive board that
  stacks on mobile.
* Accessible: labelled regions, semantic lists, real links, machine-readable dates,
  visible focus; staggered, reduced-motion-safe reveal animating only transform and
  opacity (JS gate + CSS @media), zero layout shift.
* Tuned, cached WP_Query (transient, invalidated on save_post / deleted_post /
  edited_term).
* Optional schema.org ItemList JSON-LD for the changelog.
* Self-contained: no external libraries, CDNs, fonts, or network calls.
