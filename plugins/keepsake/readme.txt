=== Keepsake — Wishlist ===
Contributors: wpaithemes
Tags: wishlist, ecommerce, save for later, products, store
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A tasteful, instant wishlist for your store — a heart on every product, a saved page, and a header count. No account required.

== Description ==

Keepsake adds the one feature every good store has: a way to save the things you
love for later. It drops a heart onto every product, keeps a running count in your
header, and gives shoppers a clean saved-items page — with zero friction and no
sign-up.

It is the wishlist done the lightweight way. Saved items live in the visitor's own
browser (localStorage), so there is no account to create, no database to fill, and
nothing to slow your store down. Hearts respond instantly, even before the page
finishes loading.

Keepsake is designed to pair with **Till — Commerce**: it hooks into Till's product
cards and single-product pages automatically, and reuses Till's product cards to
render the saved-items grid, so everything matches your store perfectly.

* **A heart on every product.** Added automatically to Till product cards and single
  products — no template editing. Tap to save, tap to remove, with a satisfying pop.
* **A saved-items page.** Activating Keepsake creates a Wishlist page. It shows real
  product cards for everything the visitor has saved, and updates live as they add or
  remove items.
* **A live header count.** Drop `keepsake_count_link()` into your theme header (the
  Emporium theme does this for you) for a wishlist icon with a saved-count badge.
* **Truly instant & private.** Saved IDs live in the browser, so reads are immediate
  and nothing about a visitor's wishlist is stored on your server or sent anywhere.
* **Matches your store.** Inherits Till's accent and styling via CSS variables; the
  heart colour is a single `--keepsake-heart` custom property you can override.
* **Self-contained.** No external libraries, fonts, CDNs, or trackers.

== Installation ==

1. Install and activate **Till — Commerce** first (Keepsake decorates Till products).
2. In wp-admin go to Plugins > Add New > Upload Plugin.
3. Choose keepsake.zip, Install Now, then Activate. A Wishlist page is created for you;
   hearts appear on every product immediately.

== Frequently Asked Questions ==

= Does it require an account or login? =

No. Wishlists are saved in the visitor's own browser, so anyone can save items
instantly without signing up.

= Does it need Till — Commerce? =

Keepsake is built for Till: it adds hearts to Till products and renders the saved page
from Till's product cards. Without Till there are no products to save.

= Where are saved items stored? =

In the browser's localStorage. Nothing is written to your database and nothing about a
visitor's wishlist leaves their device.

= How do I show the wishlist count in my header? =

Call `keepsake_count_link()` in your theme's header. The Emporium theme includes it
automatically.

= Can I change the heart colour? =

Yes — override the `--keepsake-heart` CSS custom property in your theme.

== Changelog ==

= 1.0.0 =
* Initial release.
* Heart toggle auto-added to Till product cards (corner) and single products (inline
  "Save" button) via Till's filters.
* localStorage-backed wishlist — instant, private, no account, no server storage.
* Wishlist page (auto-created) rendering real Till product cards for saved items, kept
  in sync as items are added or removed.
* `keepsake_count_link()` header helper with a live saved-count badge.
* `[keepsake_list]` shortcode; AJAX card rendering that reuses Till's card markup.
* Theme-adaptive styling; `--keepsake-heart` custom property; reduced-motion safe.
