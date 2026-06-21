=== Describe — Auto Alt Text ===
Contributors: wpaithemes
Tags: alt text, accessibility, images, seo, automation
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically gives images meaningful alt text for accessibility and SEO — zero config, no external AI, derived entirely from your own metadata.

== Description ==

Missing image alt text is the single most common accessibility failure on the web,
and it quietly hurts SEO too. **Describe** fixes it the moment you activate it —
with no settings, no API keys, and no external services. It derives meaningful alt
text locally from data WordPress already has: the attachment title, the caption,
or a cleaned-up version of the filename.

It works in two layers so both new and existing images are covered:

* **Layer 1 — on upload.** When you add an image with no alt text, Describe writes
  a real, editable value into the standard `_wp_attachment_image_alt` meta —
  derived from the title, then the caption, then a humanized filename. The alt is
  proper Media Library data from the start, not a runtime hack.
* **Layer 2 — on the front end.** Any image *still* missing alt is filled at render
  time, without writing to the database: WordPress-rendered images (featured
  images, galleries, the image block) are handled via
  `wp_get_attachment_image_attributes`, and inline `<img>` tags in your content
  are backfilled with a `the_content` pass. Older libraries become accessible
  instantly, and a one-click **Backfill** in the admin can make those values
  permanent.

What makes the derived text actually good:

* **Smart filename humanizing.** Strips the extension and WordPress size suffixes
  (`-1024x768`, `-scaled`, `-rotated`, edit revisions), removes the `-2`/`-3`
  collision counter, turns `-`/`_` into spaces, collapses whitespace, and
  sentence-cases the result — so `my-cat_on-the-sofa-1024x768.jpg` becomes
  "My cat on the sofa".
* **Knows when a filename is noise.** Camera and screenshot names like `IMG_4821`,
  `DSC00012`, `Screenshot 2024-01-14 at 10.32.45`, long hashes, and bare numbers
  carry no meaning, so Describe rejects them and tries the next source. If nothing
  is meaningful, it leaves alt empty rather than inventing gibberish.
* **Never overwrites a human.** Alt text you (or another plugin) wrote — including
  an intentional empty `alt=""` for decorative images — is always respected. Layers
  only ever fill *empty* alt.
* **Self-contained and private.** No external libraries, CDNs, web fonts, AI APIs,
  or network calls of any kind. Everything is derived locally on your server.
* **Fast.** Each layer bails early when it doesn't apply, the front-end pass skips
  content with no images, and the admin coverage figure is cached in a transient
  and rebuilt only when it changes.
* **Theme- and admin-agnostic.** The optional status panel uses scoped CSS custom
  properties with light and dark support, accessible focus states, and a
  coverage gauge whose animation respects `prefers-reduced-motion`.

Developers can shape the derivation with a single filter:

`add_filter( 'describe_alt_text', function ( $text, $attachment_id ) { return $text; }, 10, 2 );`

Other filters: `describe_alt_is_image`, `describe_alt_frontend_active`,
`describe_alt_backfill_batch_size`.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose describe-alt.zip and click Install Now.
3. Click Activate. That's it — every new image now gets alt text automatically.
4. (Optional) Visit Media > Auto Alt Text to see your coverage and backfill older
   images so their alt text is written permanently.

== Frequently Asked Questions ==

= Does it use AI or send my images anywhere? =

No. There are zero external requests. Describe does not use any AI service or
network call — alt text is derived entirely on your server from the image's title,
caption, or filename.

= Will it overwrite alt text I already wrote? =

Never. Both layers only act on images whose alt is empty. An intentional decorative
`alt=""` is also left alone.

= What about images uploaded before I installed it? =

They're handled on the front end automatically, so visitors and search engines see
alt text right away. To make those values permanent and editable in the Media
Library, run the one-click Backfill on the Media > Auto Alt Text screen (you can
run it repeatedly for very large libraries).

= How does it turn a filename into a sentence? =

It drops the extension and WordPress size/edit suffixes, removes any de-duplication
counter, replaces dashes and underscores with spaces, collapses whitespace, and
sentence-cases the result. Names that are clearly machine-generated (IMG_1234,
screenshots, hashes, bare numbers) are rejected so you never get meaningless alt.

= Does it slow down my site? =

No. Each layer bails early when it doesn't apply, the content pass skips posts with
no images, and the admin coverage stat is cached. Nothing runs on the front end
that isn't needed to fill a genuinely empty alt attribute.

= Is there anything to configure? =

No. Describe is zero-config and works the instant it's activated. The Media > Auto
Alt Text panel is purely informational plus the optional backfill button.

== Screenshots ==

1. The Auto Alt Text status panel: a coverage gauge, library stats, and the
   one-click backfill for older images.

== Changelog ==

= 1.0.0 =
* Initial release.
* Layer 1: fills empty `_wp_attachment_image_alt` on upload (and on attachment
  edit) from title, then caption, then a humanized filename.
* Layer 2: front-end backfill of still-empty alt via
  `wp_get_attachment_image_attributes` and a `the_content` inline-image pass.
* Robust local filename humanizing with size/edit-suffix stripping, counter
  removal, separator and whitespace normalization, and sentence casing.
* Meaningfulness guard that rejects camera/screenshot names, hashes, and
  number-dominated strings, falling back gracefully.
* Never overwrites human-written alt or intentional decorative `alt=""`.
* Zero config, no external services, no AI, no network calls.
* Media > Auto Alt Text status panel with a cached coverage gauge and a
  nonce-protected, capability-gated batch backfill.
* `describe_alt_text` filter (plus `describe_alt_is_image`,
  `describe_alt_frontend_active`, `describe_alt_backfill_batch_size`).
