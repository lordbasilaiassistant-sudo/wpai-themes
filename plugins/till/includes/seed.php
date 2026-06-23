<?php
/**
 * One-time install routine: create the Shop / Cart / Checkout pages and seed a
 * small, tasteful demo catalog so the store looks "lived-in" the moment it is
 * activated — in WordPress Playground and on a real site alike.
 *
 * Product imagery is generated locally with GD (no external requests, fully
 * GPL), so the demo is self-contained. If GD is unavailable the store falls
 * back to the theme's CSS placeholder and still works.
 *
 * @package Till
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the three store pages and remember their IDs, idempotently.
 */
function till_install_pages() {
	$pages = array(
		'shop'     => array(
			'title'   => __( 'Shop', 'till' ),
			'content' => '[till_shop]',
		),
		'cart'     => array(
			'title'   => __( 'Cart', 'till' ),
			'content' => '[till_cart]',
		),
		'checkout' => array(
			'title'   => __( 'Checkout', 'till' ),
			'content' => '[till_checkout]',
		),
	);

	foreach ( $pages as $key => $def ) {
		$existing = (int) get_option( 'till_page_' . $key );
		if ( $existing && 'page' === get_post_type( $existing ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $def['title'],
				'post_name'    => $key,
				'post_content' => $def['content'],
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_option( 'till_page_' . $key, $id );
		}
	}
}

/**
 * Seed the demo catalog. Skips entirely if any product already exists, so it
 * never duplicates content on re-activation or alongside a real store.
 */
function till_seed_demo_store() {
	$existing = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	if ( ! empty( $existing ) ) {
		return;
	}

	$catalog = till_demo_catalog();

	// Create categories first.
	$term_ids = array();
	foreach ( $catalog['categories'] as $slug => $name ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$term_ids[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$i = 0;
	foreach ( $catalog['products'] as $p ) {
		$i++;
		$id = wp_insert_post(
			array(
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_excerpt' => $p['excerpt'],
				'post_content' => $p['content'],
				'menu_order'   => $i,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		update_post_meta( $id, '_till_price', $p['price'] );
		if ( ! empty( $p['sale'] ) ) {
			update_post_meta( $id, '_till_sale_price', $p['sale'] );
		}
		update_post_meta( $id, '_till_sku', $p['sku'] );
		update_post_meta( $id, '_till_stock', isset( $p['stock'] ) ? $p['stock'] : '1' );
		update_post_meta( $id, '_till_rating', $p['rating'] );

		if ( isset( $term_ids[ $p['cat'] ] ) ) {
			wp_set_object_terms( $id, array( $term_ids[ $p['cat'] ] ), 'product_cat' );
		}

		$att = till_generate_product_image( $id, $p['title'], $p['cat'], $catalog['icons'] );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		}
	}
}

/**
 * The demo catalog — a small, modern homewares store ("Maison Verre").
 *
 * @return array
 */
function till_demo_catalog() {
	return array(
		'categories' => array(
			'living'      => __( 'Living', 'till' ),
			'kitchen'     => __( 'Kitchen', 'till' ),
			'lighting'    => __( 'Lighting', 'till' ),
			'accessories' => __( 'Accessories', 'till' ),
		),
		// Hue + glyph per category, used by the GD image generator.
		'icons'      => array(
			'living'      => array( 'hue' => 28, 'glyph' => 'sofa' ),
			'kitchen'     => array( 'hue' => 168, 'glyph' => 'cup' ),
			'lighting'    => array( 'hue' => 44, 'glyph' => 'lamp' ),
			'accessories' => array( 'hue' => 268, 'glyph' => 'bag' ),
		),
		'products'   => array(
			array(
				'title'   => 'Linen Lounge Cushion',
				'cat'     => 'living',
				'price'   => 48,
				'sale'    => 36,
				'sku'     => 'MV-LIV-01',
				'rating'  => 4.8,
				'excerpt' => 'A stonewashed linen cushion with a feather-soft fill, in a warm oat tone that suits any room.',
				'content' => '<p>Our best-selling cushion, cut from pre-washed European linen for that lived-in softness from the very first day. The hidden zip and removable cover make it easy to keep fresh, and the duck-feather insert holds its shape season after season.</p><ul><li>Stonewashed 100% French linen</li><li>Feather-down insert included</li><li>50 × 50 cm · hidden zip closure</li></ul>',
			),
			array(
				'title'   => 'Curved Oak Side Table',
				'cat'     => 'living',
				'price'   => 189,
				'sku'     => 'MV-LIV-02',
				'rating'  => 4.6,
				'excerpt' => 'A solid white-oak side table with a soft curved edge and a hand-rubbed oil finish.',
				'content' => '<p>Turned from a single piece of solid white oak and finished by hand with a natural plant oil that lets the grain breathe. The gently curved top is forgiving on elbows and the eye alike.</p><ul><li>Solid FSC-certified white oak</li><li>Hand-oiled, food-safe finish</li><li>45 cm tall · 40 cm diameter</li></ul>',
			),
			array(
				'title'   => 'Boucle Accent Chair',
				'cat'     => 'living',
				'price'   => 420,
				'sale'    => 350,
				'sku'     => 'MV-LIV-03',
				'rating'  => 4.9,
				'excerpt' => 'A sculptural lounge chair wrapped in cream bouclé on a powder-coated steel base.',
				'content' => '<p>The chair that makes the room. A deep, enveloping seat in plush ivory bouclé floats on a slim powder-coated frame. Comfortable enough to read in for hours, handsome enough to leave empty.</p><ul><li>Heavyweight recycled-fibre bouclé</li><li>Powder-coated steel base</li><li>Holds up to 140 kg</li></ul>',
			),
			array(
				'title'   => 'Stoneware Mug Set',
				'cat'     => 'kitchen',
				'price'   => 38,
				'sku'     => 'MV-KIT-01',
				'rating'  => 4.7,
				'excerpt' => 'Four reactive-glaze stoneware mugs, each one a little different from the last.',
				'content' => '<p>Thrown from speckled stoneware and dipped in a reactive glaze that pools and breaks over the rim, so no two mugs are quite alike. Generous 350 ml capacity and a handle that actually fits your fingers.</p><ul><li>Set of four · 350 ml each</li><li>Dishwasher and microwave safe</li><li>Reactive matte glaze</li></ul>',
			),
			array(
				'title'   => 'Acacia Serving Board',
				'cat'     => 'kitchen',
				'price'   => 54,
				'sale'    => 42,
				'sku'     => 'MV-KIT-02',
				'rating'  => 4.5,
				'excerpt' => 'A long acacia board with a leather hang loop — equally at home with cheese or bread.',
				'content' => '<p>Rich, warm acacia with a dramatic grain, finished with a juice groove and a soft leather loop for hanging. Long enough for a proper grazing spread, light enough to carry one-handed.</p><ul><li>Solid acacia hardwood</li><li>50 × 18 cm with juice groove</li><li>Vegetable-tanned leather loop</li></ul>',
			),
			array(
				'title'   => 'Matte Black Kettle',
				'cat'     => 'kitchen',
				'price'   => 79,
				'sku'     => 'MV-KIT-03',
				'rating'  => 4.4,
				'excerpt' => 'A gooseneck pour-over kettle with a precise spout and a soft-touch matte finish.',
				'content' => '<p>Designed for the slow morning ritual. The slim gooseneck gives you a steady, controllable pour for coffee or tea, and the matte black body wipes clean with a cloth.</p><ul><li>1.0 L stainless steel</li><li>Precision gooseneck spout</li><li>Stovetop and induction ready</li></ul>',
			),
			array(
				'title'   => 'Paper Globe Pendant',
				'cat'     => 'lighting',
				'price'   => 95,
				'sku'     => 'MV-LGT-01',
				'rating'  => 4.6,
				'excerpt' => 'A handmade rice-paper pendant that throws a soft, diffuse glow over a table.',
				'content' => '<p>A modern take on the classic paper lantern, ribbed by hand over a steel frame for a sculptural silhouette by day and a warm, even light by night. Flat-packs for easy hanging.</p><ul><li>45 cm rice-paper globe</li><li>2 m braided fabric cord</li><li>E27 fitting · bulb not included</li></ul>',
			),
			array(
				'title'   => 'Brass Task Lamp',
				'cat'     => 'lighting',
				'price'   => 145,
				'sale'    => 119,
				'sku'     => 'MV-LGT-02',
				'rating'  => 4.8,
				'excerpt' => 'An adjustable desk lamp in aged brass with a weighted base and a warm dimmable LED.',
				'content' => '<p>A proper task lamp with the heft to match. The aged-brass arm articulates smoothly and stays exactly where you put it, while the built-in dimmable LED runs cool and sips power.</p><ul><li>Solid aged-brass arm</li><li>Integrated dimmable warm LED</li><li>Weighted, non-slip base</li></ul>',
			),
			array(
				'title'   => 'Ceramic Table Lamp',
				'cat'     => 'lighting',
				'price'   => 128,
				'sku'     => 'MV-LGT-03',
				'rating'  => 4.3,
				'stock'   => '0',
				'excerpt' => 'A hand-glazed ceramic base with a natural linen drum shade. Currently sold out.',
				'content' => '<p>A quietly confident bedside lamp: a rounded, hand-glazed ceramic base topped with a natural linen shade that softens the light beautifully. A perennial favourite — back in stock soon.</p><ul><li>Hand-glazed ceramic base</li><li>Natural linen drum shade</li><li>In-line cord switch</li></ul>',
			),
			array(
				'title'   => 'Woven Market Tote',
				'cat'     => 'accessories',
				'price'   => 42,
				'sku'     => 'MV-ACC-01',
				'rating'  => 4.7,
				'excerpt' => 'A roomy woven tote with leather handles — for the market, the beach, or the everyday.',
				'content' => '<p>Hand-woven from durable recycled cord with full-grain leather handles that soften as they age. Holds far more than it looks, and folds flat when it is not in use.</p><ul><li>Recycled woven cord</li><li>Full-grain leather handles</li><li>Holds up to 12 kg</li></ul>',
			),
			array(
				'title'   => 'Merino Throw Blanket',
				'cat'     => 'accessories',
				'price'   => 110,
				'sale'    => 88,
				'sku'     => 'MV-ACC-02',
				'rating'  => 4.9,
				'excerpt' => 'A featherweight merino throw with a fringed edge, warm without the weight.',
				'content' => '<p>Spun from fine-grade merino for warmth that feels like almost nothing across your lap. The hand-knotted fringe and heathered tone make it as good over the sofa arm as over your shoulders.</p><ul><li>100% fine merino wool</li><li>130 × 180 cm · hand-knotted fringe</li><li>Naturally temperature-regulating</li></ul>',
			),
			array(
				'title'   => 'Glass Carafe & Tumbler',
				'cat'     => 'accessories',
				'price'   => 34,
				'sku'     => 'MV-ACC-03',
				'rating'  => 4.5,
				'excerpt' => 'A handblown bedside carafe with a tumbler that nests neatly on top.',
				'content' => '<p>A small luxury for the nightstand: a handblown carafe with a tumbler that doubles as a lid, so a glass of water is always within reach and never gathering dust.</p><ul><li>Handblown lead-free glass</li><li>500 ml carafe · 200 ml tumbler</li><li>Dishwasher safe</li></ul>',
			),
		),
	);
}

/**
 * Generate a tasteful studio-style product image with GD and attach it.
 *
 * @param int    $post_id Product ID.
 * @param string $title   Product title (drawn small at the foot).
 * @param string $cat     Category slug.
 * @param array  $icons   Category => [hue, glyph] map.
 * @return int Attachment ID, or 0 on failure.
 */
function till_generate_product_image( $post_id, $title, $cat, $icons ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagejpeg' ) ) {
		return 0;
	}

	$meta  = isset( $icons[ $cat ] ) ? $icons[ $cat ] : array( 'hue' => 200, 'glyph' => 'bag' );
	$hue   = (int) $meta['hue'];
	$glyph = $meta['glyph'];

	$size = 1000;
	$im   = imagecreatetruecolor( $size, $size );

	// Vertical gradient background: a soft, light wash of the category hue.
	list( $r1, $g1, $b1 ) = till_hsl_to_rgb( $hue, 0.30, 0.95 );
	list( $r2, $g2, $b2 ) = till_hsl_to_rgb( $hue, 0.34, 0.83 );
	for ( $y = 0; $y < $size; $y++ ) {
		$t = $y / $size;
		$r = (int) round( $r1 + ( $r2 - $r1 ) * $t );
		$g = (int) round( $g1 + ( $g2 - $g1 ) * $t );
		$b = (int) round( $b1 + ( $b2 - $b1 ) * $t );
		$col = imagecolorallocate( $im, $r, $g, $b );
		imageline( $im, 0, $y, $size, $y, $col );
	}

	// A soft contact-shadow ellipse under the motif.
	$shadow = imagecolorallocatealpha( $im, 0, 0, 0, 105 );
	imagefilledellipse( $im, (int) ( $size * 0.5 ), (int) ( $size * 0.74 ), (int) ( $size * 0.46 ), (int) ( $size * 0.10 ), $shadow );

	// The product motif, in a deeper tone of the hue.
	list( $dr, $dg, $db ) = till_hsl_to_rgb( $hue, 0.45, 0.40 );
	$ink  = imagecolorallocate( $im, $dr, $dg, $db );
	list( $lr, $lg, $lb ) = till_hsl_to_rgb( $hue, 0.50, 0.66 );
	$tint = imagecolorallocate( $im, $lr, $lg, $lb );

	till_draw_glyph( $im, $glyph, $size, $ink, $tint );

	// Save to the uploads directory and register an attachment.
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		imagedestroy( $im );
		return 0;
	}
	$filename = 'till-' . $post_id . '-' . sanitize_title( $title ) . '.jpg';
	$path     = trailingslashit( $upload['path'] ) . $filename;

	imagejpeg( $im, $path, 86 );
	imagedestroy( $im );

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$attachment = array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => $title,
		'post_status'    => 'inherit',
		'guid'           => trailingslashit( $upload['url'] ) . $filename,
	);
	$att_id = wp_insert_attachment( $attachment, $path, $post_id );
	if ( ! $att_id || is_wp_error( $att_id ) ) {
		return 0;
	}
	$data = wp_generate_attachment_metadata( $att_id, $path );
	wp_update_attachment_metadata( $att_id, $data );
	return $att_id;
}

/**
 * Draw a simple, recognisable line-art product glyph centred on the canvas.
 *
 * @param resource|GdImage $im    Image.
 * @param string           $glyph Glyph key.
 * @param int              $size  Canvas size.
 * @param int              $ink   Stroke colour.
 * @param int              $tint  Fill colour.
 */
function till_draw_glyph( $im, $glyph, $size, $ink, $tint ) {
	if ( function_exists( 'imagesetthickness' ) ) {
		imagesetthickness( $im, max( 8, (int) ( $size * 0.012 ) ) );
	}
	$cx = $size * 0.5;
	$cy = $size * 0.46;
	$s  = $size * 0.30; // motif half-extent.

	switch ( $glyph ) {
		case 'sofa':
			// Backrest + seat + arms.
			imagefilledrectangle( $im, (int) ( $cx - $s ), (int) ( $cy - $s * 0.5 ), (int) ( $cx + $s ), (int) ( $cy + $s * 0.55 ), $tint );
			imagefilledrectangle( $im, (int) ( $cx - $s ), (int) ( $cy ), (int) ( $cx + $s ), (int) ( $cy + $s * 0.55 ), $ink );
			imagefilledrectangle( $im, (int) ( $cx - $s ), (int) ( $cy - $s * 0.5 ), (int) ( $cx - $s * 0.78 ), (int) ( $cy + $s * 0.7 ), $ink );
			imagefilledrectangle( $im, (int) ( $cx + $s * 0.78 ), (int) ( $cy - $s * 0.5 ), (int) ( $cx + $s ), (int) ( $cy + $s * 0.7 ), $ink );
			break;

		case 'cup':
			// Mug body + handle.
			imagefilledellipse( $im, (int) $cx, (int) ( $cy - $s * 0.55 ), (int) ( $s * 1.2 ), (int) ( $s * 0.4 ), $tint );
			imagefilledrectangle( $im, (int) ( $cx - $s * 0.6 ), (int) ( $cy - $s * 0.55 ), (int) ( $cx + $s * 0.6 ), (int) ( $cy + $s * 0.6 ), $ink );
			imagefilledellipse( $im, (int) $cx, (int) ( $cy + $s * 0.6 ), (int) ( $s * 1.2 ), (int) ( $s * 0.4 ), $ink );
			imagearc( $im, (int) ( $cx + $s * 0.7 ), (int) ( $cy ), (int) ( $s * 0.8 ), (int) ( $s * 0.8 ), -80, 80, $ink );
			break;

		case 'lamp':
			// Conical shade + stem + base.
			$pts = array(
				(int) ( $cx - $s * 0.7 ), (int) ( $cy - $s * 0.15 ),
				(int) ( $cx + $s * 0.7 ), (int) ( $cy - $s * 0.15 ),
				(int) ( $cx + $s * 0.45 ), (int) ( $cy - $s * 0.85 ),
				(int) ( $cx - $s * 0.45 ), (int) ( $cy - $s * 0.85 ),
			);
			if ( PHP_VERSION_ID >= 80100 ) {
				imagefilledpolygon( $im, $pts, $tint );
			} else {
				imagefilledpolygon( $im, $pts, 4, $tint );
			}
			imagefilledrectangle( $im, (int) ( $cx - $s * 0.05 ), (int) ( $cy - $s * 0.15 ), (int) ( $cx + $s * 0.05 ), (int) ( $cy + $s * 0.7 ), $ink );
			imagefilledrectangle( $im, (int) ( $cx - $s * 0.45 ), (int) ( $cy + $s * 0.7 ), (int) ( $cx + $s * 0.45 ), (int) ( $cy + $s * 0.82 ), $ink );
			break;

		case 'bag':
		default:
			// Tote: body + two handles.
			imagefilledrectangle( $im, (int) ( $cx - $s * 0.7 ), (int) ( $cy - $s * 0.35 ), (int) ( $cx + $s * 0.7 ), (int) ( $cy + $s * 0.75 ), $tint );
			imagefilledrectangle( $im, (int) ( $cx - $s * 0.7 ), (int) ( $cy - $s * 0.35 ), (int) ( $cx + $s * 0.7 ), (int) ( $cy - $s * 0.18 ), $ink );
			imagearc( $im, (int) ( $cx - $s * 0.32 ), (int) ( $cy - $s * 0.35 ), (int) ( $s * 0.5 ), (int) ( $s * 0.6 ), 180, 360, $ink );
			imagearc( $im, (int) ( $cx + $s * 0.32 ), (int) ( $cy - $s * 0.35 ), (int) ( $s * 0.5 ), (int) ( $s * 0.6 ), 180, 360, $ink );
			break;
	}
}

/**
 * Convert HSL (h in 0–360, s/l in 0–1) to an [r,g,b] triplet (0–255).
 *
 * @param float $h Hue.
 * @param float $s Saturation.
 * @param float $l Lightness.
 * @return array{0:int,1:int,2:int}
 */
function till_hsl_to_rgb( $h, $s, $l ) {
	$h /= 360;
	if ( 0 === (int) ( $s * 1000 ) ) {
		$v = (int) round( $l * 255 );
		return array( $v, $v, $v );
	}
	$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
	$p = 2 * $l - $q;
	$rgb = array();
	foreach ( array( $h + 1 / 3, $h, $h - 1 / 3 ) as $tc ) {
		if ( $tc < 0 ) {
			$tc += 1;
		}
		if ( $tc > 1 ) {
			$tc -= 1;
		}
		if ( $tc < 1 / 6 ) {
			$c = $p + ( $q - $p ) * 6 * $tc;
		} elseif ( $tc < 1 / 2 ) {
			$c = $q;
		} elseif ( $tc < 2 / 3 ) {
			$c = $p + ( $q - $p ) * ( 2 / 3 - $tc ) * 6;
		} else {
			$c = $p;
		}
		$rgb[] = (int) round( $c * 255 );
	}
	return $rgb;
}
