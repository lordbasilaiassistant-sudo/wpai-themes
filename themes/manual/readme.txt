=== Manual ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, two-columns, left-sidebar, custom-menu, custom-logo, featured-images, threaded-comments, translation-ready, editor-style, block-styles, light, accessibility-ready, documentation

A clean, developer-friendly documentation theme — a sticky left-hand docs navigation tree, a comfortable reading column, prominent search, a version label, and beautifully set code blocks.

== Description ==

Manual is a calm, precise WordPress theme built for documentation and knowledge
bases. A sticky left-hand navigation tree keeps every section a click away, the
single reading column sits at a comfortable measure, and code blocks render on a
deep slate surface with a copy button. A prominent search and a version label in
the header give the whole site the feel of a real product manual.

Its signature is an auto-built "On this page" rail that tracks the heading you
are currently reading (active-section tracking) and smooth-scrolls to a section
when clicked — no plugin required, and it disappears gracefully without
JavaScript or on short articles.

It ships with real CSS — custom properties, a modular type scale, a responsive
docs layout, and accessible focus states — and uses only system fonts, so it
loads instantly with no external requests.

Features:

* Sticky left-hand documentation navigation tree, from a dedicated "Docs" menu
  or built automatically from your categories and pages
* Signature "On this page" rail with active-section tracking and smooth anchor
  scrolling (progressive enhancement: nothing breaks without JS)
* Beautiful code blocks on a deep slate surface, with one-click copy buttons
* Prominent search in both the header and the docs hero, with a version label
* Documentation hero on the home, a featured lead doc, and a two-up card grid
* Featured images everywhere, with a graceful blueprint-grid placeholder when a
  post has none, so the rhythm never breaks
* Comfortable reading measure, system-sans UI, readable serif prose, mono code
* Threaded comments rendered as "notes", and a polished comment form
* Accessible, responsive navigation with no-JS fallbacks for every toggle
* Skip link, visible focus-visible states, semantic landmarks, AA contrast, and
  full prefers-reduced-motion support
* Customizer "Colors & Style" controls (Accent, Background, Surface) with live
  preview, plus a documentation version label
* Block editor color palette and font sizes via theme.json
* Custom logo, primary + docs + footer menus, and a styled 404
* Translation-ready (text domain: manual). No external fonts, no tracking.

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose manual.zip and click Install Now.
3. Click Activate.
4. Set a "Docs Navigation (left rail)" menu under Appearance > Menus for a
   curated section tree (otherwise Manual builds one from your categories and
   pages), assign a Primary menu, set your version label and colors under
   Appearance > Customize > "Colors & Style", and give your docs featured
   images for the best look.

== Frequently Asked Questions ==

= How do I build the left navigation tree? =

Create a menu under Appearance > Menus, add your sections (and nested pages
beneath them), and assign it to the "Docs Navigation (left rail)" location.
Until then, Manual builds a sensible tree from your categories and pages.

= Where does the "On this page" list come from? =

It is generated automatically from the H2 and H3 headings inside your article.
It needs at least two headings to appear, and is removed cleanly if JavaScript
is unavailable.

= How do I set the version label? =

Appearance > Customize > "Colors & Style" > Version label. Leave it blank to
hide the chip beside your site title.

== Changelog ==

= 1.0.0 =
* Initial release.
