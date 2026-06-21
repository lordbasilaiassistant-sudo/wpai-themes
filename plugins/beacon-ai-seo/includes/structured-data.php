<?php
/**
 * JSON-LD structured data (@graph) for Beacon — AI & SEO.
 *
 * Emits a single schema.org @graph in the head describing the site and the
 * current view to search engines and AI agents. Nodes are linked by @id so
 * crawlers can resolve relationships:
 *
 *   - WebSite       (with a SearchAction potential action),
 *   - Organization  or Person (the site's publisher, from name + site icon),
 *   - Article/BlogPosting + BreadcrumbList on single posts.
 *
 * Only nodes that have real data are added. Output is encoded with
 * wp_json_encode (which escapes for safe inline <script> embedding) and the
 * surrounding tag is fixed/trusted.
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Stable @id for the site-wide WebSite node.
 *
 * @return string
 */
function beacon_website_id() {
	return home_url( '/#website' );
}

/**
 * Stable @id for the publisher (Organization or Person) node.
 *
 * @return string
 */
function beacon_publisher_id() {
	return home_url( '/#publisher' );
}

/**
 * Build the WebSite node, including a SearchAction for sitelinks search box.
 *
 * @return array
 */
function beacon_node_website() {
	$node = array(
		'@type'     => 'WebSite',
		'@id'       => beacon_website_id(),
		'url'       => home_url( '/' ),
		'name'      => beacon_site_name(),
		'publisher' => array( '@id' => beacon_publisher_id() ),
	);

	$tagline = beacon_site_tagline();
	if ( '' !== $tagline ) {
		$node['description'] = $tagline;
	}

	$node['potentialAction'] = array(
		array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$node['inLanguage'] = get_bloginfo( 'language' );

	return $node;
}

/**
 * Build the publisher node: Person if the site represents an individual,
 * otherwise Organization. Adds the site icon as the logo/image when present.
 *
 * @return array
 */
function beacon_node_publisher() {
	$is_person = 'person' === beacon_get_entity_type();

	$node = array(
		'@type' => $is_person ? 'Person' : 'Organization',
		'@id'   => beacon_publisher_id(),
		'name'  => beacon_site_name(),
		'url'   => home_url( '/' ),
	);

	$icon = get_site_icon_url( 512 );
	if ( $icon ) {
		$logo = array(
			'@type'      => 'ImageObject',
			'@id'        => home_url( '/#logo' ),
			'url'        => $icon,
			'contentUrl' => $icon,
		);

		// Organizations use `logo`; both benefit from a generic `image`.
		if ( ! $is_person ) {
			$node['logo'] = $logo;
		}
		$node['image'] = array( '@id' => home_url( '/#logo' ) );
	}

	$profiles = beacon_get_social_profiles();
	if ( ! empty( $profiles ) ) {
		$node['sameAs'] = array_values( $profiles );
	}

	return $node;
}

/**
 * Build the Article/BlogPosting node for a single post.
 *
 * Includes headline, description, dates, author (as a Person), the featured
 * image, and mainEntityOfPage pointing at the canonical URL.
 *
 * @param int $post_id The post ID.
 * @return array|null Node array, or null if the post is unavailable.
 */
function beacon_node_article( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$permalink = get_permalink( $post );

	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => $permalink . '#article',
		'isPartOf'         => array( '@id' => beacon_website_id() ),
		'mainEntityOfPage' => array( '@id' => $permalink ),
		'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
		'datePublished'    => get_post_time( DATE_W3C, true, $post ),
		'dateModified'     => get_post_modified_time( DATE_W3C, true, $post ),
		'publisher'        => array( '@id' => beacon_publisher_id() ),
		'inLanguage'       => get_bloginfo( 'language' ),
	);

	$description = beacon_post_summary( $post, 40 );
	if ( '' !== $description ) {
		$node['description'] = $description;
	}

	$author = get_the_author_meta( 'display_name', (int) $post->post_author );
	if ( $author ) {
		$node['author'] = array(
			'@type' => 'Person',
			'name'  => wp_strip_all_tags( $author ),
			'url'   => get_author_posts_url( (int) $post->post_author ),
		);
	}

	if ( has_post_thumbnail( $post ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'full' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			$node['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $src[0],
				'width'  => isset( $src[1] ) ? (int) $src[1] : null,
				'height' => isset( $src[2] ) ? (int) $src[2] : null,
			);
			$node['image'] = array_filter( $node['image'], static function ( $v ) {
				return null !== $v;
			} );
		}
	}

	return $node;
}

/**
 * Build a BreadcrumbList node from Home → (category) → current post.
 *
 * @param int $post_id The post ID.
 * @return array|null
 */
function beacon_node_breadcrumb( $post_id ) {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$items    = array();
	$position = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => __( 'Home', 'beacon-ai-seo' ),
		'item'     => home_url( '/' ),
	);

	$cats = get_the_category( $post->ID );
	if ( ! empty( $cats ) && $cats[0] instanceof WP_Term ) {
		$link = get_category_link( $cats[0] );
		if ( $link && ! is_wp_error( $link ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => wp_strip_all_tags( $cats[0]->name ),
				'item'     => $link,
			);
		}
	}

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position,
		'name'     => wp_strip_all_tags( get_the_title( $post ) ),
		'item'     => get_permalink( $post ),
	);

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => get_permalink( $post ) . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * Assemble the full @graph for the current request.
 *
 * @return array The list of nodes (already real-data-filtered).
 */
function beacon_build_graph() {
	$graph = array(
		beacon_node_website(),
		beacon_node_publisher(),
	);

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();

		$article = beacon_node_article( $post_id );
		if ( $article ) {
			$graph[] = $article;
		}

		$breadcrumb = beacon_node_breadcrumb( $post_id );
		if ( $breadcrumb ) {
			$graph[] = $breadcrumb;
		}
	}

	/**
	 * Filter the assembled JSON-LD @graph node list before output.
	 *
	 * @param array $graph The list of schema.org nodes.
	 */
	return (array) apply_filters( 'beacon_jsonld_graph', $graph );
}

/**
 * Print the JSON-LD <script> block in the head.
 *
 * Encoded with wp_json_encode using flags that keep slashes and unicode intact
 * and escape the output so it is safe to embed inline. The script tag itself is
 * a fixed, trusted literal.
 *
 * @return void
 */
function beacon_print_jsonld() {
	if ( ! beacon_should_output_head() ) {
		return;
	}

	$graph = beacon_build_graph();

	if ( empty( $graph ) ) {
		return;
	}

	$document = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	$json = wp_json_encode( $document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( false === $json ) {
		return;
	}

	echo '<script type="application/ld+json">' . $json . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode escapes for inline embedding.
}
add_action( 'wp_head', 'beacon_print_jsonld', 2 );
