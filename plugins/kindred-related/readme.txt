=== Kindred — Related Posts ===
Contributors: wpaithemes
Tags: related posts, posts, engagement, accessibility, recommendations
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A tasteful "You might also like" section after single posts. Theme-adaptive, accessible, cached, zero configuration.

== Description ==

Kindred adds a polished related-posts section after the content of single posts.
It picks up to three relatives by shared categories first, then shared tags, then
the most recent posts as a fallback — so the section is never empty on a site with
content. It works on any theme, adds no settings page, makes no external requests,
and stores nothing beyond a short-lived cache.

* **Relevant, in that order.** Posts that share a category come first (the strongest
  signal), topped up with posts that share a tag, then the most recent posts. The
  current post and any unpublished posts are always excluded.
* **Fast.** The related IDs for each post are computed with a tuned `WP_Query`
  (IDs only, `no_found_rows`, no term/meta cache priming) and cached in a transient
  for ~12 hours. The cache is invalidated automatically when you save the post, so
  pages stay snappy without serving stale picks.
* **Theme-adaptive.** Colors derive from the surrounding text via `currentColor`,
  so the cards look right on both light and dark themes. Themes can override the
  `--kindred-*` custom properties to restyle the whole section.
* **Graceful images.** Posts with a featured image show it (lazy-loaded, with a real
  alt attribute). Posts without one get a CSS gradient placeholder showing the
  title's first letter — no broken image, no blank box, no extra HTTP request.
* **Accessible.** The section is a labelled region (`aria-labelledby`), each card is
  a single real link, dates carry a machine-readable `datetime`, focus is visible,
  and `prefers-reduced-motion` is honored at every layer — reduced-motion visitors
  get the cards with no animation at all.
* **Crafted entrance.** As the section scrolls into view the cards fade and slide up
  in a gentle stagger. The motion animates only `transform` and `opacity` (no layout
  shift) and is pure progressive enhancement — with JavaScript off, or if it fails,
  the cards are simply visible from the start.
* **Self-contained.** No external libraries, CDNs, web fonts, network calls, or
  tracking. The scroll reveal is hand-rolled with vanilla `IntersectionObserver`.

Place it manually anywhere in a template:

`<?php if ( function_exists( 'kindred_related_posts' ) ) { kindred_related_posts(); } ?>`

…or drop the shortcode into post content:

`[kindred_related]`

Developers can tune the behavior with filters:

`add_filter( 'kindred_posts_count', fn() => 4 );` — show four cards instead of three.
`add_filter( 'kindred_heading', fn() => 'Related reading' );` — change the heading.
`add_filter( 'kindred_auto_append', '__return_false' );` — stop auto-appending (place it manually).

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose kindred-related.zip and click Install Now.
3. Click Activate. That's it — open any post to see the related section.

== Frequently Asked Questions ==

= How are related posts chosen? =

Kindred looks for posts that share a category first, then tops up with posts that
share a tag, then with the most recent posts. The current post and unpublished posts
are always excluded.

= Does it work on dark themes? =

Yes. Card surfaces, borders, and text tint themselves from the current text color
rather than hardcoded grays, so the section adapts to light and dark themes
automatically.

= Can I place it somewhere other than after the content? =

Yes. Call `kindred_related_posts()` in your template, or use the `[kindred_related]`
shortcode. To stop the automatic after-content placement, add
`add_filter( 'kindred_auto_append', '__return_false' );`.

= Does it slow down my site or make network requests? =

No. The related IDs are cached per post in a transient for ~12 hours and recomputed
only when you save the post. There are no external requests and no settings page.

== Changelog ==

= 1.0.0 =
* Initial release.
* Related posts after single posts by shared categories, then tags, then recency.
* Tuned, cached WP_Query (transient, ~12h, invalidated on save_post).
* Responsive, theme-adaptive card grid with featured images and a graceful gradient
  placeholder (first letter) when a post has none.
* Accessible labelled region, real links, machine-readable dates, visible focus.
* Staggered, reduced-motion-safe scroll-in reveal animating only transform/opacity.
* Manual placement via the `kindred_related_posts()` template tag and the
  `[kindred_related]` shortcode.
