<?php
/**
 * <head> meta tags for Beacon — AI & SEO.
 *
 * Prints, at an early priority so they sit near the top of the document head:
 *   - a meta description,
 *   - a canonical link,
 *   - a robots directive,
 *   - Open Graph tags (type/title/description/url/image/site_name), and
 *   - Twitter summary_large_image card tags.
 *
 * Every value is resolved by the shared helpers and escaped at output. Tags are
 * only emitted when they carry real data, so empty descriptions or missing
 * images never produce hollow tags.
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Print one `<meta name="…" content="…">` tag if the content is non-empty.
 *
 * @param string $name    The meta name attribute.
 * @param string $content The content (raw; escaped here).
 * @return void
 */
function beacon_meta_name( $name, $content ) {
	$content = trim( (string) $content );

	if ( '' === $content ) {
		return;
	}

	printf(
		"<meta name=\"%s\" content=\"%s\" />\n",
		esc_attr( $name ),
		esc_attr( $content )
	);
}

/**
 * Print one `<meta property="…" content="…">` tag if the content is non-empty.
 *
 * Used for Open Graph, which keys on `property` rather than `name`.
 *
 * @param string $property The OG property (e.g. "og:title").
 * @param string $content  The content (raw; escaped here).
 * @return void
 */
function beacon_meta_property( $property, $content ) {
	$content = trim( (string) $content );

	if ( '' === $content ) {
		return;
	}

	printf(
		"<meta property=\"%s\" content=\"%s\" />\n",
		esc_attr( $property ),
		esc_attr( $content )
	);
}

/**
 * Output the full set of SEO/social meta tags in the document head.
 *
 * Hooked early (priority 1) on `wp_head` so the description, canonical, and
 * social tags appear near the top of the head. The whole block is short-
 * circuited when Beacon should defer to another SEO plugin or the context is
 * not a front-end page (see beacon_should_output_head()).
 *
 * @return void
 */
function beacon_print_meta_tags() {
	if ( ! beacon_should_output_head() ) {
		return;
	}

	$title       = beacon_get_title();
	$description = beacon_get_description();
	$canonical   = beacon_get_canonical();
	$image       = beacon_get_image();
	$site_name   = beacon_site_name();

	echo "\n<!-- Beacon — AI & SEO -->\n";

	// Standard meta.
	beacon_meta_name( 'description', $description );
	beacon_meta_name( 'robots', beacon_get_robots() );

	if ( $canonical ) {
		printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $canonical ) );
	}

	// Open Graph.
	beacon_meta_property( 'og:type', is_singular( 'post' ) ? 'article' : 'website' );
	beacon_meta_property( 'og:title', $title );
	beacon_meta_property( 'og:description', $description );
	beacon_meta_property( 'og:url', $canonical );
	beacon_meta_property( 'og:site_name', $site_name );

	$locale = get_bloginfo( 'language' );
	if ( $locale ) {
		beacon_meta_property( 'og:locale', str_replace( '-', '_', $locale ) );
	}

	if ( $image ) {
		beacon_meta_property( 'og:image', $image );
	}

	// Article-specific Open Graph for single posts.
	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();

		$published = get_post_time( DATE_W3C, true, $post_id );
		$modified  = get_post_modified_time( DATE_W3C, true, $post_id );

		if ( $published ) {
			beacon_meta_property( 'article:published_time', $published );
		}
		if ( $modified ) {
			beacon_meta_property( 'article:modified_time', $modified );
		}
	}

	// Twitter Card.
	beacon_meta_name( 'twitter:card', $image ? 'summary_large_image' : 'summary' );
	beacon_meta_name( 'twitter:title', $title );
	beacon_meta_name( 'twitter:description', $description );

	if ( $image ) {
		beacon_meta_name( 'twitter:image', $image );
	}

	$twitter_handle = beacon_twitter_handle();
	if ( $twitter_handle ) {
		beacon_meta_name( 'twitter:site', $twitter_handle );
		beacon_meta_name( 'twitter:creator', $twitter_handle );
	}

	echo "<!-- /Beacon -->\n\n";
}
add_action( 'wp_head', 'beacon_print_meta_tags', 1 );

/**
 * Resolve a Twitter/X @handle from the saved social profile URLs, if present.
 *
 * Looks through the configured profiles for a twitter.com / x.com URL and
 * extracts the @handle. Returns '' when none is configured.
 *
 * @return string The handle including the leading "@", or ''.
 */
function beacon_twitter_handle() {
	foreach ( beacon_get_social_profiles() as $url ) {
		if ( preg_match( '#https?://(?:www\.)?(?:twitter|x)\.com/@?([A-Za-z0-9_]{1,15})#', $url, $m ) ) {
			return '@' . $m[1];
		}
	}

	return '';
}
