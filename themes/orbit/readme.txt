=== Orbit ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, business, landing, two-columns, right-sidebar, custom-menu, custom-logo, featured-images, threaded-comments, translation-ready, editor-style, block-styles, dark, accessibility-ready

A bold dark dev-tool and startup theme — an animated starfield/orbit hero, count-up metrics, a feature grid, and big magnetic CTAs on a deep space-navy canvas.

== Description ==

Orbit is a high-energy, dark WordPress theme for product launches, developer
tools, and startup engineering blogs. It opens with a confident homepage: an
animated starfield/orbit hero, a strip of count-up metrics, a three-up feature
grid, and a clean changelog-style list of your latest posts — all on a deep
space-navy near-black canvas lit by a glowing neon-cyan accent.

It ships with real CSS — custom properties, a modular type scale, luminous
cards, code-friendly typography, and a sticky frosted header — and uses only
system/web-safe fonts, so it loads instantly with no external requests.

Features:

* Product-launch homepage: animated starfield/orbit hero, count-up metrics,
  and a three-up feature grid, followed by a featured lead story and a card list
* The signature trio: a drifting orbit/starfield in the hero, a magnetic CTA
  whose glow tracks the cursor, and metrics that count up when scrolled into view
* Featured images on the index, single, and pages — with a graceful nebula
  gradient placeholder when a post has none, so the rhythm never breaks
* Category pills, bylines, and dates that organise posts at a glance
* Dark-optimised reading layout: comfortable measure, mono-accented meta and
  code blocks, glowing blockquotes
* Two-column layout with a sticky, widgetised sidebar (Search, Recent Posts,
  Recent Comments, Archives, Categories all styled)
* Threaded comments and a polished comment form
* Accessible, responsive mobile navigation with a no-JS fallback
* Skip link, visible :focus-visible states, semantic landmarks, AA contrast,
  and full prefers-reduced-motion support
* Live Customizer color controls (Accent, Background, Surface) with instant
  preview; accent shades derive automatically via color-mix()
* Native WPAI companion-plugin placement via wpai_entry_top / wpai_entry_bottom
* Block editor color palette, gradients, and font sizes via theme.json
* Custom logo, primary + footer menus, and a styled 404
* Translation-ready (text domain: orbit). No external fonts, no tracking.

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose orbit.zip and click Install Now.
3. Click Activate.
4. Set a Primary menu under Appearance > Menus, add widgets under
   Appearance > Widgets, set a site tagline (it powers the hero copy), and give
   your posts featured images for the best look.

== Frequently Asked Questions ==

= Where does the hero headline come from? =

The hero pulls the highlighted product name from your Site Title and its
supporting line from your Site Tagline (Settings > General, or the Customizer).
If no tagline is set, Orbit shows a polished default.

= Are the metric numbers editable? =

The metrics strip ships with sensible launch-day defaults defined in
functions.php (orbit_render_metrics). Edit that array to set your own values
and labels; the count-up animation adapts to each number's precision.

= Does it work without JavaScript? =

Yes. All content, the final metric values, and the orbit/starfield artwork are
present without JS. The starfield drift, magnetic CTA, count-up, and scroll
reveals are progressive enhancements that also respect prefers-reduced-motion.

== Changelog ==

= 1.0.0 =
* Initial release. Bold dark dev-tool / startup theme with an animated
  starfield/orbit hero, count-up metrics, a feature grid, and magnetic CTAs on
  a deep space-navy canvas with a neon-cyan accent.
* Two-column reading layout with a sticky frosted header and styled sidebar,
  pagination, post navigation, comments, search, and 404.
* Live Customizer color controls with color-mix() derived accent shades and a
  cohesive, accessible motion system (deferred, footer-loaded).
* Native WPAI companion-plugin integration via wpai_entry_top /
  wpai_entry_bottom action hooks, with a matching dark "native skin".
* theme.json palette, gradients, and font sizes aligned with the stylesheet.
