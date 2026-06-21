=== Atelier ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: portfolio, two-columns, custom-menu, custom-logo, featured-images, threaded-comments, translation-ready, editor-style, block-styles, light, accessibility-ready

A refined designer-studio portfolio theme — a gallery-like project grid, a sophisticated neutral palette with one restrained oxblood accent, and big, quiet imagery.

== Description ==

Atelier is a refined, gallery-like WordPress theme for designers, studios, and
makers. The home opens with a short studio statement, then settles into a large
project grid: the newest piece becomes a full-width feature plate and the rest
tile beneath it, each with its cover image, category kicker, and a quiet
caption. It is elegant rather than loud — generous whitespace, big imagery, an
editorial serif-and-sans pairing, charcoal ink on warm bone paper, and a single
muted oxblood accent used sparingly.

Its two signature moves give the work a sense of being unveiled: every project
cover performs a clip-path reveal — wiping in from a thin slit to its full frame
as it scrolls into view — and a refined caption follows the cursor while it
hovers a cover, naming the piece. Both are pure progressive enhancement: they
animate only transform, opacity, and clip-path, are gated behind a JS flag, and
switch off entirely for prefers-reduced-motion and touch.

It ships with real CSS — custom properties, a modular type scale, a responsive
gallery grid, and accessible focus states — and uses only system/web-safe
fonts, so it loads instantly with no external requests.

Features:

* Gallery home: a short studio statement, a full-width feature plate, and a
  large two-up project grid that stays elegant from 360px to wide screens
* Signature clip-path image reveal — each cover wipes in on scroll
* Signature cursor-following caption that names the work on hover (pointer-only)
* Featured images on the grid, single, and pages — with a graceful tonal
  placeholder when a piece has none, so the rhythm never breaks
* Category kickers, bylines, and frame indices that organise the grid at a glance
* Comfortable reading measure, serif body type, and balanced headings on posts
* Two-column layout with a sticky, widgetised sidebar
* A deep "ink" footer field that anchors the page
* Threaded comments and a polished comment form
* Accessible, responsive mobile navigation with a no-JS fallback
* Skip link, visible focus-visible states, semantic landmarks, AA contrast,
  and full prefers-reduced-motion support
* Customizer color controls (Accent, Background, Ink) with instant live preview
* Block editor color palette and font sizes via theme.json
* Native WPAI companion-plugin placement via wpai_entry_top / wpai_entry_bottom
* Custom logo, primary + footer menus, and a styled 404
* Translation-ready (text domain: atelier). No external fonts, no tracking.

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose atelier.zip and click Install Now.
3. Click Activate.
4. Set a Primary menu under Appearance > Menus, add widgets under
   Appearance > Widgets, and give your projects featured images for the best look.

== Frequently Asked Questions ==

= How do I change the accent color? =

Go to Appearance > Customize > "Colors & Style" and pick a new Accent, Background,
or Ink color. The lighter and darker accent shades follow automatically, and the
whole theme updates live as you choose.

= Why do my project covers slide in as I scroll? =

That is Atelier's signature clip-path reveal. It is decorative and fully
accessible: it never hides content without JavaScript, and it switches off for
visitors who prefer reduced motion.

== Changelog ==

= 1.0.0 =
* Initial release.
