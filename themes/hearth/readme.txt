=== Hearth ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: business, restaurant, food-and-drink, two-columns, right-sidebar, custom-menu, custom-logo, featured-images, threaded-comments, translation-ready, editor-style, block-styles, custom-colors, accessibility-ready

A warm, appetizing theme for restaurants, cafés, and neighborhood hospitality — a welcoming hero, a live "today's hours" card, and a tactile menu grid.

== Description ==

Hearth is a cozy, trustworthy WordPress theme built for local food and drink
businesses. The home page opens with an inviting hero — your tagline, an
intro, two clear calls-to-action, and a warm photo frame — anchored by Hearth's
signature "Today's hours" card that shows whether you're open right now,
computed live from your site's own timezone. Below, your latest posts plate up
into a tactile menu/offerings grid of dish cards.

Long-form posts (your story, events, recipes) read on a comfortable measure in
an elegant serif, with accent-ruled headings, warm blockquotes, and tidy lists.
Everything sits in an appetizing palette of terracotta, toasted cream, and herb
olive, and ships with real CSS — custom properties, a modular type scale, a
responsive two-column layout, and accessible focus states — using only
system/web-safe fonts, so it loads instantly with no external requests.

Features:

* Welcoming home hero with tagline, intro, dual CTAs, and a warm photo frame
* Signature "Today's hours" card with a live open/closed status pip, computed
  server-side from your timezone (correct even before JavaScript runs)
* Tactile menu/offerings grid of dish cards with category, excerpt, and date —
  with a graceful warm gradient placeholder when a post has no image
* Comfortable reading measure, serif body type, and accent-ruled headings for
  storytelling and photo-forward posts
* Two-column layout with a sticky, widgetised sidebar (Search, Recent Posts,
  Recent Comments, Archives, Categories all styled as warm cards)
* Threaded comments and a polished comment form
* Accessible, responsive mobile navigation with a no-JS fallback
* Skip link, visible focus-visible states, semantic landmarks, AA contrast,
  and full prefers-reduced-motion support
* Live Customizer color controls (Accent, Background, Herb/Open) with instant
  preview; accent and olive shades derive automatically via color-mix()
* Block editor color palette and font sizes via theme.json
* Custom logo, primary + footer menus, and a styled 404
* Native integration with the free WPAI companion plugins
* Translation-ready (text domain: hearth). No external fonts, no tracking.

== Customizing your hours ==

The "Today's hours" card ships with sensible café hours. Developers can wire in
real opening hours via the `hearth_service_hours` filter (see functions.php) —
each day is a label, an hours string, and open/close minutes-from-midnight used
to compute the live open/closed status.

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose hearth.zip and click Install Now.
3. Click Activate.
4. Set a Primary menu under Appearance > Menus, add widgets under
   Appearance > Widgets, set your site tagline (it leads the hero), and give
   your posts featured images for the most appetizing look.

== Changelog ==

= 1.0.0 =
* Initial release.
