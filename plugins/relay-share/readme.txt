=== Relay — Social Share ===
Contributors: wpaithemes
Tags: social share, share buttons, privacy, engagement, accessibility
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tasteful, privacy-first social share buttons after single posts. No tracking scripts, theme-adaptive, accessible, zero configuration.

== Description ==

Relay adds a polished row of share buttons after the content of single posts —
Copy link, X, Bluesky, LinkedIn, Mastodon, and Email. Every button is a plain
web-intent URL or an inline SVG icon. There are **no third-party widgets, no
tracking pixels, no SDKs, and no external requests** — your readers' visit to
your post never pings a social network until they choose to share.

* **Privacy first.** Each network is reached with its own well-known share URL
  built on your server; nothing loads from Facebook, X, LinkedIn, or anyone
  else. No cookies are set, no scripts are pulled from a CDN, no analytics fire.
* **Copy link, done right.** The Copy button writes the post URL to the clipboard
  and shows a brief, accessible "Copied!" confirmation announced to screen
  readers via an `aria-live` region. It uses the modern Clipboard API with a
  textarea fallback and, if both are blocked, selects the URL so you can copy it
  by hand.
* **Mastodon without a central host.** Because Mastodon is federated there is no
  single share URL. Relay prompts for the visitor's instance (remembered for the
  session) and opens that instance's share intent.
* **Theme-adaptive.** Icons and pill surfaces tint themselves from the
  surrounding text via `currentColor`, so the bar looks right on both light and
  dark themes. Override the `--relay-*` custom properties to restyle the whole
  bar.
* **Accessible.** The bar is a labelled region, each control is a real link or
  button with a clear `aria-label`, the copy confirmation uses a polite live
  region, focus is visible, and `prefers-reduced-motion` is honored at every
  layer — reduced-motion visitors get no movement at all.
* **No layout shift.** The hover lift animates only `transform`, and the
  "Copied!" confirmation cross-fades in place over the button — the bar never
  reflows.
* **Self-contained.** No external libraries, CDNs, web fonts, network calls, or
  tracking. Icons are hand-authored inline SVG; the clipboard and Mastodon logic
  is vanilla JavaScript.

**Native theme integration.** When the active theme declares
`add_theme_support( 'wpai-companions' )`, the share bar renders on the theme's
`wpai_entry_bottom` hook (outside the prose column, full article width) instead
of being appended inside `the_content`. On every other theme it is appended after
the post content automatically. It is never rendered twice.

Place it manually anywhere in a template:

`<?php if ( function_exists( 'relay_share' ) ) { relay_share(); } ?>`

…or drop the shortcode into post content:

`[relay_share]`

Developers can tune the behavior with filters:

`add_filter( 'relay_heading', fn() => 'Spread the word' );` — change the heading.
`add_filter( 'relay_networks', fn() => array( 'copy', 'x', 'email' ) );` — pick / reorder networks.
`add_filter( 'relay_auto_append', '__return_false' );` — stop auto-appending (place it manually).
`add_filter( 'relay_is_active', fn( $a ) => $a || is_page() );` — also show on pages.

Supported network keys for `relay_networks`: `copy`, `x`, `bluesky`, `linkedin`,
`mastodon`, `email`.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose relay-share.zip and click Install Now.
3. Click Activate. That's it — open any post to see the share bar.

== Frequently Asked Questions ==

= Does it load anything from social networks? =

No. Every share button is a plain link built on your server (or, for Copy and
Mastodon, handled in the browser). Nothing is loaded from a third party, no
tracking scripts run, and no cookies are set. Your readers stay private until
they choose to share.

= Why does Mastodon ask for an instance? =

Mastodon is federated — there is no single mastodon.com to share through. Relay
asks for your reader's home instance once per session and opens that server's
share intent.

= Does the Copy button work everywhere? =

It uses the modern Clipboard API where available (most browsers, on HTTPS), falls
back to a hidden-textarea copy on older browsers, and, if both are blocked,
selects the URL on screen so the reader can copy it manually. The "Copied!"
confirmation is announced to screen readers.

= Does it work on dark themes? =

Yes. Icons, labels, borders, and button surfaces tint themselves from the current
text color rather than hardcoded grays, so the bar adapts to light and dark
themes automatically.

= Can I place it somewhere other than after the content? =

Yes. Call `relay_share()` in your template, or use the `[relay_share]` shortcode.
To stop the automatic after-content placement, add
`add_filter( 'relay_auto_append', '__return_false' );`.

= Can I choose which networks appear? =

Yes. Use the `relay_networks` filter to return a subset (and order) of the
supported keys: `copy`, `x`, `bluesky`, `linkedin`, `mastodon`, `email`.

== Changelog ==

= 1.0.0 =
* Initial release.
* Privacy-first share bar after single posts: Copy link, X, Bluesky, LinkedIn,
  Mastodon, and Email — plain share URLs and inline SVG icons, no tracking.
* Copy link with an accessible "Copied!" confirmation (aria-live), Clipboard API
  with a textarea and manual-select fallback.
* Mastodon support that prompts for the visitor's instance (session-remembered).
* Native theme integration: renders on `wpai_entry_bottom` for themes that
  support `wpai-companions`, otherwise appends to the_content — never twice.
* Theme-adaptive, currentColor-driven styling for light and dark themes.
* Accessible labelled region, real links/buttons, visible focus.
* Reduced-motion-safe hover and confirmation animating only transform/opacity,
  zero layout shift.
* Manual placement via the `relay_share()` template tag and the `[relay_share]`
  shortcode; `relay_heading`, `relay_networks`, `relay_auto_append`, and
  `relay_is_active` filters.
