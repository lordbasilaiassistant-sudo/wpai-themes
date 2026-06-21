=== Weave — Auto Internal Links ===
Contributors: wpaithemes
Tags: internal links, automation, seo, interlinking, related posts
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically weave a web of internal links with zero effort. Theme-adaptive, accessible, cached, zero configuration.

== Description ==

Weave reads your published posts and, on every single post, automatically hyperlinks
the first mention of your *other* posts' titles to those posts. The result is a dense,
relevant web of internal links — the kind editors hand-build for SEO and reader
flow — created and maintained for you, with no settings to touch.

Activate it and it just runs. There is no settings page, no shortcode to place, no
network calls, and nothing stored beyond a short-lived cache.

* **Truly automatic.** It builds a dictionary of every published post title once,
  caches it, and links the first whole-word match of each title inside your content.
  Publish a new post and the web re-weaves itself — old posts start linking to the new
  one automatically.
* **Surgically safe.** Weave never links inside an existing link, a heading
  (`h1`–`h6`), or `code`/`pre`/`kbd` blocks; it never touches HTML attributes; and it
  never links a post to itself. It parses without a DOM extension — it isolates the
  visible text between protected regions and only ever rewrites that.
* **Tasteful by default.** Matching is whole-word and case-insensitive, the longest
  (most specific) titles win, each title links at most once, and the number of
  auto-links per post is capped (five by default) so your content reads as edited, not
  spammed.
* **Theme-adaptive.** Auto-links get a subtle, faint dotted underline that firms up on
  hover and focus. Colors derive from your theme via `currentColor` and CSS custom
  properties, so they look right on both light and dark themes — and you can override
  `.weave-link` or the `--weave-*` variables to restyle them entirely.
* **Accessible.** Real anchors, a visible keyboard focus ring, and motion that honors
  `prefers-reduced-motion`. Only `color` and `text-decoration-color` animate, so there
  is zero layout shift.
* **Fast.** The title dictionary is the only expensive work, and it is cached in a
  transient and rebuilt automatically when you save or delete a post — so visitors
  never pay for it. Weave bails out instantly on any view that is not a single post.
* **Self-contained.** No external libraries, CDNs, web fonts, APIs, or tracking.
  Everything is derived locally from your own content.

Developers can target their own destinations with a keyword => URL map:

`add_filter( 'weave_dictionary', function ( $map ) {
    $map['affiliate marketing'] = home_url( '/guides/affiliate/' );
    $map['our pricing']         = home_url( '/pricing/' );
    return $map;
} );`

…and tune the behavior with filters:

`add_filter( 'weave_max_links', fn() => 8 );` — allow up to eight auto-links per post.
`add_filter( 'weave_min_title_length', fn() => 6 );` — only match titles 6+ chars long.
`add_filter( 'weave_is_active', '__return_false' );` — disable on a given request.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose weave-links.zip and click Install Now.
3. Click Activate. That's it — open any post with two or more published posts on the
   site and you'll see the internal links woven in automatically.

== Frequently Asked Questions ==

= Do I have to configure anything? =

No. Weave has no settings page. Activate it and it immediately starts linking the first
mention of your other post titles on every single post.

= Will it create weird or broken links? =

No. Weave never links inside existing links, headings, or code blocks, never touches
HTML attributes, never links a post to itself, and caps the number of links per post.
Matching is whole-word, so "art" never gets linked inside "start" or "artist".

= How does it pick what to link? =

For each post it scans the visible text and links the first whole-word, case-insensitive
match of every *other* published post's title. When two titles overlap, the longer (more
specific) one wins. Each title is linked at most once, and the total per post is capped
at five by default (filterable).

= Can I link to pages or external URLs too? =

Yes. Use the `weave_dictionary` filter to add your own keyword => URL pairs. They take
precedence over post titles with the same text and can point anywhere.

= Does it slow down my site or make network requests? =

No. The title dictionary is cached in a transient and rebuilt only when you save or
delete a post. There are no external requests, and Weave does nothing at all on views
that are not single posts.

= How do I change how the links look? =

Auto-links carry the class `weave-link`. Style that class in your theme, or override the
`--weave-accent`, `--weave-underline`, and `--weave-underline-hover` custom properties.

== Changelog ==

= 1.0.0 =
* Initial release.
* Automatic first-occurrence internal links to other published posts on single posts.
* Cached title dictionary (transient, ~12h) rebuilt eagerly on save_post / deleted_post.
* Longest-title-first, whole-word, case-insensitive matching with Unicode word
  boundaries.
* Never links inside existing links, headings (h1–h6), or code/pre/kbd; never links a
  post to itself; capped per post (default 5) with at most one link per target.
* DOM-extension-free safe parser that isolates and rewrites only visible text.
* Optional developer keyword => URL map via the `weave_dictionary` filter.
* Subtle, theme-adaptive dotted-underline styling (light & dark), accessible focus,
  reduced-motion safe.
* Filters: weave_max_links, weave_dictionary, weave_min_title_length, weave_is_active.
