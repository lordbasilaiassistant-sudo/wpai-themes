=== Beacon — AI & SEO ===
Contributors: wpaithemes
Tags: seo, ai, structured data, json-ld, llms.txt, open graph, schema, meta tags
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your site discoverable by search engines AND AI agents. Auto meta tags, Open Graph, Twitter cards, JSON-LD, and a machine-readable /llms.txt. Zero config.

== Description ==

Beacon is a free, premium-quality SEO plugin built for two audiences at once: the
search engines that have always crawled the web, and the LLMs and AI agents that
increasingly read it. Activate it and your site is instantly more legible to both —
no setup required.

**For search engines and social, in every page head:**

* A clean meta description (from the excerpt, else trimmed content), a canonical
  link, and a sensible robots directive.
* Open Graph tags (`og:type`, `og:title`, `og:description`, `og:url`, `og:image`,
  `og:site_name`, locale, and article published/modified times on posts).
* Twitter Card tags (`summary_large_image` when a featured image exists, otherwise
  `summary`), with the creator handle derived from your social profiles.
* A linked JSON-LD `@graph` of schema.org structured data: a **WebSite** node with
  a SearchAction, an **Organization** or **Person** publisher built from your site
  name and site icon, and on single posts an **Article/BlogPosting** node
  (headline, description, dates, author as a Person, image, mainEntityOfPage) plus
  a **BreadcrumbList**.

Every value is computed automatically from the current post, page, or archive and
the featured image, every value is escaped on output, and **only nodes and tags
that have real data are emitted** — no hollow tags, ever.

**For AI agents — the signature feature — `/llms.txt`:**

Beacon serves a clean, machine-readable Markdown document at your site's
`/llms.txt`. It opens with your site name as an H1 and tagline as a blockquote,
then lists your **Pages** and **Recent posts**, each as
`- [Title](absolute-url): one-line excerpt`. AI agents and LLMs can fetch this one
file to understand your whole site at a glance.

It is served robustly under **any** permalink structure: Beacon detects the request
early (and registers a rewrite rule, flushed on activation), outputs `text/plain`,
and exits. The generated document is cached in a transient for six hours and
refreshed automatically whenever you save a post.

**Premium where it counts, free where it matters:**

* **Theme-agnostic.** The optional settings page tints itself from the current
  admin colors via `currentColor`, so it looks right in light, dark, and custom
  admin schemes.
* **Accessible.** Semantic markup, an ARIA live region for status, visible focus,
  and full `prefers-reduced-motion` support.
* **Self-contained.** No external libraries, CDNs, fonts, network calls, or
  tracking of any kind. Vanilla PHP, CSS, and JavaScript only.
* **Plays well with others.** If a major SEO plugin (Yoast, Rank Math, All in One
  SEO, SEOPress, The SEO Framework) is detected, Beacon steps aside on the head
  output by default so you never get duplicate tags — while keeping `/llms.txt`.

**Developer filters:**

`add_filter( 'beacon_output_head', '__return_false' );`            // Disable head output.
`add_filter( 'beacon_description', fn( $d ) => $d );`              // Customize the meta description.
`add_filter( 'beacon_image', fn( $url ) => $url );`               // Customize the social image.
`add_filter( 'beacon_jsonld_graph', fn( $graph ) => $graph );`    // Add/modify JSON-LD nodes.
`add_filter( 'beacon_llms_body', fn( $body ) => $body );`         // Customize the /llms.txt body.
`add_filter( 'beacon_llms_cache_ttl', fn() => HOUR_IN_SECONDS );` // Change the cache window.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose beacon-ai-seo.zip and click Install Now.
3. Click Activate. That's it — your meta tags and JSON-LD are live, and your
   `/llms.txt` is being served immediately.
4. (Optional) Visit Settings > Beacon AI & SEO to choose Organization vs. Person
   and add your social profile URLs.

== Frequently Asked Questions ==

= What is /llms.txt and why does my site need one? =

`/llms.txt` is an emerging convention — like `robots.txt`, but for AI. It gives
LLMs and AI agents a clean, machine-readable Markdown summary of your site so they
can cite and reference your content accurately. Beacon generates and serves it for
you automatically.

= Will it conflict with my existing SEO plugin? =

No. Beacon detects the major SEO plugins and, by default, defers its head output to
them so you never get duplicate meta tags or competing structured data. The
`/llms.txt` feature keeps working regardless. You can force Beacon on or off with
the `beacon_output_head` filter.

= Does it require any configuration? =

No. Beacon produces correct output the moment it is activated. The settings page is
entirely optional and only fine-tunes the publisher entity type and your social
`sameAs` links.

= Does it make external requests or store personal data? =

No. Beacon is fully self-contained — no external libraries, CDNs, fonts, network
calls, or tracking. It only caches its own generated `/llms.txt` body in a
transient.

= My /llms.txt shows a 404. What do I do? =

Beacon serves the route even without flushed rewrite rules, but if your site has an
unusual setup, visit Settings > Permalinks and click Save Changes once to flush the
rules. Re-activating the plugin also flushes them.

== Changelog ==

= 1.0.0 =
* Initial release.
* Automatic meta description, canonical, and robots tags in the document head.
* Open Graph and Twitter `summary_large_image` card tags from the current view and
  featured image.
* JSON-LD `@graph`: WebSite (with SearchAction), Organization/Person publisher,
  and Article/BlogPosting + BreadcrumbList on single posts.
* Signature `/llms.txt`: a machine-readable Markdown index of pages and recent
  posts for LLMs and AI agents, served under any permalink structure, cached for
  six hours and refreshed on save.
* Best-effort detection of other SEO plugins to avoid duplicate output, plus
  developer filters for the description, image, JSON-LD graph, and /llms.txt body.
* Optional, theme-agnostic settings page (Organization vs. Person, social
  profiles) with an accessible, motion-safe copy-to-clipboard helper.
