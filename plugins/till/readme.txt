=== Till — Commerce ===
Contributors: wpaithemes
Tags: ecommerce, shop, store, cart, products, checkout, woocommerce alternative
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A complete, self-contained store for WordPress — products, a slide-in cart, and a clean checkout. No external services, no monthly fee.

== Description ==

Till turns any WordPress site into a real online store in a single click. Activate
it and you get a fully stocked demo shop — twelve products across four categories,
each with its own generated studio image, price, rating, and stock status — plus a
Shop page, a Cart page, and a Checkout page, all created for you automatically.

It is built to be the lightweight, no-strings alternative to the heavyweight commerce
plugins: no account to create, no upsell screens, no external API calls, and nothing
that costs money. Everything runs locally on your own site, and it is 100% GPL.

Pair it with the free **Emporium** theme for a polished, Shopify-grade storefront, or
drop its shortcodes into any theme you like.

* **A real product type.** Till registers a `product` post type with a Pricing &
  Inventory panel (price, sale price, SKU, stock, rating), product categories, and an
  admin price column. Single-product pages get a clean gallery, price, rating,
  quantity stepper, add-to-cart button, and "you might also like" related products —
  in any theme.
* **A slide-in cart.** Add to cart anywhere and a drawer slides in with your items, a
  live subtotal, quantity steppers, and remove links. The header cart count updates
  instantly with a satisfying little bump. It is all progressive enhancement — the
  cart and checkout pages work fully without JavaScript.
* **A clean checkout.** A two-column checkout with an order summary and a contact form.
  Placing an order records it under Products → Orders and shows a friendly
  confirmation. It is a *demo* checkout — clearly labelled, no card is charged, and no
  data leaves your site.
* **Self-contained, gorgeous imagery.** Demo products get tasteful, studio-style images
  generated locally with PHP's GD library — no stock-photo licensing, no external
  requests. Missing an image? The store falls back to a clean tonal placeholder and
  keeps working.
* **Shortcodes for everything.** `[till_shop]` (filterable, sortable, paginated grid),
  `[till_featured count="4"]` (a row for landing pages), `[till_cart]`, and
  `[till_checkout]`.
* **Theme-adaptive styling.** The whole store is recoloured by a handful of CSS
  variables (`--till-accent`, `--till-sale`, `--till-radius`…), so it inherits your
  theme's look. It ships beautiful out of the box.
* **Genuinely private.** No trackers, no analytics, no CDNs, no web fonts, no network
  calls. The cart lives in a cookie on the visitor's own browser; nothing is sent
  anywhere.

Developers can extend it with filters:

`add_filter( 'till_currency', fn() => '€' );` — change the currency symbol.
`add_filter( 'till_card_corner', $html, $id );` — inject markup into a product card
(used by the Keepsake wishlist plugin to add a save-to-wishlist heart).
`add_filter( 'till_single_buy', $html, $id );` — add controls beside add-to-cart.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose till.zip and click Install Now.
3. Click Activate. Till creates your Shop, Cart, and Checkout pages and stocks a demo
   catalog automatically. Visit /shop to see it. Add Shop to your menu and you're live.

== Frequently Asked Questions ==

= Do I need WooCommerce? =

No. Till is a complete, standalone store. It does not depend on WooCommerce or any
other plugin, and it makes no external requests.

= Is the checkout real? =

It is a fully working order flow — it records orders and shows a confirmation — but it
is a *demo* checkout by design: no payment gateway is connected and no card is charged.
It is perfect for demos, catalogs, and "request a quote" style stores, and it is a
clean foundation to wire a gateway onto.

= Where do the product images come from? =

Till generates them on your own server with PHP's GD library when you activate it, so
there is no licensing to worry about and no external request. Replace any of them by
setting a featured image on the product, just like a normal post.

= Will it duplicate products if I reactivate it? =

No. The demo seeder only runs when your store has zero products, so it never duplicates
content or interferes with a real catalog.

= How do I change the colors? =

Override the CSS custom properties (e.g. `--till-accent`, `--till-sale`) in your theme,
or just use the Emporium theme, which is designed to drive them from its Customizer.

= Does it work in WordPress Playground? =

Yes — that's exactly how the live preview on the WPAI Themes gallery runs. It is fully
self-contained.

== Changelog ==

= 1.0.0 =
* Initial release.
* `product` post type with Pricing & Inventory meta (price, sale, SKU, stock, rating),
  `product_cat` taxonomy, and an admin price column.
* Cookie-backed cart (no PHP sessions) with AJAX add/update/remove, a slide-in drawer,
  a live header count, and a confirmation toast — all progressive enhancement.
* Single-product layout injected into any theme: gallery, price, rating, quantity,
  add-to-cart, related products.
* `[till_shop]` (category filter, sort, pagination), `[till_featured]`, `[till_cart]`,
  `[till_checkout]` shortcodes.
* Demo checkout that records a private `shop_order` and clears the cart.
* One-click install: auto-creates Shop / Cart / Checkout pages and seeds a 12-product
  demo catalog with GD-generated studio imagery (idempotent — only seeds an empty store).
* Theme-adaptive styling via CSS custom properties; no external requests, fonts, or
  trackers.
* Filters: till_currency, till_card_corner, till_single_buy.
