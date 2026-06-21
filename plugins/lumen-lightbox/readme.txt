=== Lumen — Image Lightbox ===
Contributors: wpaithemes
Tags: lightbox, gallery, images, accessibility, media
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An accessible, self-contained image lightbox. Click any content or gallery image to open a smooth full-screen viewer with keyboard navigation, a focus trap, and captions. Zero configuration.

== Description ==

Lumen turns the images in your posts and pages into a polished, accessible
full-screen lightbox. Click (or press Enter/Space on) any eligible image and it
opens in a focused overlay with the caption from the figure or the alt text. When
a post has several images, arrow keys and on-screen prev/next buttons move between
them. Escape closes the viewer and returns focus to exactly where you were.

It works on any theme, adds no settings page, makes no external requests, bundles
no libraries, and stores nothing. The overlay, the navigation, the focus trap and
the animation are all hand-rolled in vanilla JavaScript and CSS.

* **Truly accessible.** The overlay is a real dialog (`role="dialog"`,
  `aria-modal="true"`) with a proper focus trap, full keyboard support
  (ArrowLeft/ArrowRight to navigate, Home/End to jump, Escape to close), a polite
  live region that announces the current position, visible focus rings on every
  control, and focus restoration to the triggering image on close.
* **Smart captions.** Each image's caption comes from its enclosing
  `<figcaption>` when present, falling back to the image's `alt` text — resolved
  server-side so the script has nothing to guess at.
* **Full-size on open.** Lumen opens the largest available source: an
  attachment's original file (via the `wp-image-{id}` class), the biggest
  `srcset` candidate, or the displayed image with its WordPress size suffix
  stripped — so a thumbnail in the post opens crisp and full-size.
* **Motion that respects you.** The fade + zoom animates only `transform` and
  `opacity` (GPU-friendly, zero layout shift). `prefers-reduced-motion` is
  honored at both layers — the JavaScript guard and the CSS `@media` block — so
  reduced-motion visitors get an instant, animation-free open and close.
* **Theme-agnostic.** The viewer is a self-contained dark surface that reads the
  same on every site; the in-content trigger affordances (focus ring, zoom
  cursor) derive from `currentColor`. Scoped `--lumen-*` custom properties let a
  site retune anything.
* **Self-contained.** No external libraries, CDNs, web fonts, network calls, or
  tracking. Icons are inline, hand-authored SVG. The assets load only on singular
  front-end views and the script is deferred, so it never blocks rendering.
* **Stays out of the way.** Lumen skips images that are already links, tiny icons
  and emoji, SVGs, and anything marked to opt out. It bails entirely on the
  admin, feeds, embeds, and REST requests.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose lumen-lightbox.zip and click Install Now.
3. Click Activate. That's it — open any post with images and click one.

== Frequently Asked Questions ==

= Which images become clickable? =

Content and gallery images on single posts and pages. Lumen skips images that are
already wrapped in a link (the author already chose a click target), SVGs, very
small images (icons/emoji), and any image marked with a `data-lumen-skip`
attribute or a `lumen-skip` CSS class.

= Where does the caption come from? =

From the image's enclosing `<figcaption>` if there is one; otherwise from the
image's `alt` text. If neither exists, the viewer simply shows no caption.

= Does it open the full-size image? =

Yes. Lumen finds the largest available source — the attachment's original file,
the biggest `srcset` candidate, or the displayed URL with its WordPress size
suffix removed — so a small in-post image still opens crisp and full-size.

= Is it accessible? =

Yes. The overlay is a focus-trapped dialog with `aria-modal`, full keyboard
navigation (arrows, Home/End, Escape), a polite live region for position
announcements, visible focus, and focus restoration to the image you clicked.

= Does it respect reduced motion? =

Yes. When the visitor's system requests reduced motion, the fade and zoom are
disabled in both the JavaScript and the CSS — the viewer opens and closes
instantly.

= Can I turn it off for a specific post? =

Yes. Set a custom field named `lumen_disable` to `1` (or true/yes/on) on that
post. Developers can also use the `lumen_is_active` and
`lumen_is_disabled_for_post` filters.

= Does it load any external resources or slow my site down? =

No. There are no external requests, no libraries, and no web fonts. The small CSS
and JavaScript load only on singular front-end views, and the script is deferred
so it never blocks rendering.

== Screenshots ==

1. The full-screen lightbox: a focused image with its caption, a close button,
   prev/next navigation, and an image counter.

== Changelog ==

= 1.0.0 =
* Initial release.
* Click/keyboard-activated full-screen lightbox for content and gallery images.
* Accessible dialog: role="dialog", aria-modal, focus trap, focus restoration.
* Keyboard navigation: ArrowLeft/ArrowRight, Home/End, Escape; on-screen prev/next.
* Captions from figcaption with an alt-text fallback; polite live-region counter.
* Opens the largest available source (attachment original, srcset, or size-suffix
  stripped) for a crisp full-size view.
* Fade + zoom animating only transform/opacity, with prefers-reduced-motion
  honored in both JS and CSS (instant when reduced).
* Self-contained vanilla PHP/JS/CSS — no libraries, CDNs, fonts, or network calls.
* Skips already-linked images, SVGs, tiny icons, and opted-out images; bails on
  admin, feeds, embeds, and REST. Per-post disable via the `lumen_disable` meta.
