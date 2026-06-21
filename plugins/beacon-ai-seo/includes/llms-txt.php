<?php
/**
 * /llms.txt — the signature AI feature of Beacon — AI & SEO.
 *
 * Serves a clean, machine-readable Markdown document at the site root path
 * /llms.txt so LLMs and AI agents can understand the site at a glance:
 *
 *   # Site Name
 *   > Tagline
 *
 *   ## Pages
 *   - [Title](url): one-line excerpt
 *
 *   ## Recent posts
 *   - [Title](url): one-line excerpt
 *
 * Robustness is the priority. The route is served two ways so it works under
 * ANY permalink structure:
 *   1. A rewrite rule (registered on init, flushed on activation/deactivation).
 *   2. A direct request-path check on `parse_request`, which fires even when
 *      pretty permalinks are off or the rewrite has not been flushed.
 *
 * The generated body is cached in a 6-hour transient and invalidated whenever a
 * post is saved, so generation cost is paid at most once per cache window.
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * The transient key for the cached /llms.txt body.
 */
const BEACON_LLMS_TRANSIENT = 'beacon_llms_txt';

/**
 * Register the /llms.txt rewrite rule.
 *
 * Called on `init` (so the rule is present on every request) and again from the
 * activation hook right before a flush. Uses the index.php?beacon_llms=1 query
 * so the request resolves to our template_redirect handler.
 *
 * @return void
 */
function beacon_register_llms_rewrite() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?beacon_llms=1', 'top' );
}
add_action( 'init', 'beacon_register_llms_rewrite' );

/**
 * Register the custom query var the rewrite rule sets.
 *
 * @param array $vars Existing public query vars.
 * @return array
 */
function beacon_llms_query_var( $vars ) {
	$vars[] = 'beacon_llms';

	return $vars;
}
add_filter( 'query_vars', 'beacon_llms_query_var' );

/**
 * Early, permalink-independent detection of a request for /llms.txt.
 *
 * Runs on `parse_request` (before the main query) and inspects the resolved
 * request path. This catches the route even when pretty permalinks are off or
 * the rewrite rules have not been flushed, making the feature work the moment
 * the plugin is active.
 *
 * @param WP $wp The WordPress environment instance.
 * @return void
 */
function beacon_maybe_serve_llms( $wp ) {
	$path = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';

	// Fall back to the raw URI when $wp->request is empty (e.g. plain permalinks).
	if ( '' === $path && isset( $_SERVER['REQUEST_URI'] ) ) {
		$raw  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$uri  = wp_parse_url( $raw, PHP_URL_PATH );
		$path = trim( (string) $uri, '/' );
	}

	if ( 'llms.txt' === strtolower( $path ) ) {
		beacon_serve_llms_txt();
	}
}
add_action( 'parse_request', 'beacon_maybe_serve_llms' );

/**
 * Handle the rewrite-driven route on template_redirect.
 *
 * When the rewrite rule matched, the `beacon_llms` query var is set and we serve
 * the document here. This is the flushed-permalinks fast path; the
 * parse_request check above is the no-flush safety net.
 *
 * @return void
 */
function beacon_template_redirect_llms() {
	if ( '1' === (string) get_query_var( 'beacon_llms' ) ) {
		beacon_serve_llms_txt();
	}
}
add_action( 'template_redirect', 'beacon_template_redirect_llms' );

/**
 * Output the /llms.txt document as text/plain and exit.
 *
 * Sends a 200 with no-cache-busting but cache-friendly headers, prints the
 * (cached) body, and terminates the request so no theme template runs.
 *
 * @return void
 */
function beacon_serve_llms_txt() {
	$body = beacon_get_llms_body();

	if ( ! headers_sent() ) {
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=' . ( get_bloginfo( 'charset' ) ?: 'UTF-8' ) );
		header( 'X-Robots-Tag: noindex, follow' );
		header( 'X-Content-Type-Options: nosniff' );
	}

	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text body assembled from escaped/normalized parts.
	exit;
}

/**
 * Return the /llms.txt body, generating and caching it when needed.
 *
 * @return string
 */
function beacon_get_llms_body() {
	$cached = get_transient( BEACON_LLMS_TRANSIENT );

	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$body = beacon_generate_llms_body();

	/**
	 * Filter the cache lifetime (in seconds) for the generated /llms.txt body.
	 *
	 * @param int $ttl Lifetime in seconds. Default 6 hours.
	 */
	$ttl = (int) apply_filters( 'beacon_llms_cache_ttl', 6 * HOUR_IN_SECONDS );

	set_transient( BEACON_LLMS_TRANSIENT, $body, max( 60, $ttl ) );

	return $body;
}

/**
 * Generate the Markdown body for /llms.txt.
 *
 * Structure:
 *   # Site name
 *   > Tagline (blockquote)
 *
 *   ## Pages          (published pages, menu_order then title)
 *   - [Title](url): excerpt
 *
 *   ## Recent posts   (latest published posts)
 *   - [Title](url): excerpt
 *
 * @return string
 */
function beacon_generate_llms_body() {
	$lines = array();

	$name = beacon_site_name();
	$lines[] = '# ' . ( '' !== $name ? $name : home_url( '/' ) );

	$tagline = beacon_site_tagline();
	if ( '' !== $tagline ) {
		$lines[] = '';
		$lines[] = '> ' . $tagline;
	}

	$lines[] = '';
	$lines[] = '> ' . sprintf(
		/* translators: %s: site home URL. */
		__( 'This file is provided for LLMs and AI agents. Home: %s', 'beacon-ai-seo' ),
		home_url( '/' )
	);

	/**
	 * Filter how many pages are listed in /llms.txt.
	 *
	 * @param int $count Default 50.
	 */
	$page_count = (int) apply_filters( 'beacon_llms_page_count', 50 );

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => max( 0, $page_count ),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'has_password'   => false,
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $pages ) ) {
		$lines[] = '';
		$lines[] = '## ' . __( 'Pages', 'beacon-ai-seo' );
		foreach ( $pages as $page ) {
			$lines[] = beacon_llms_list_item( $page );
		}
	}

	/**
	 * Filter how many recent posts are listed in /llms.txt.
	 *
	 * @param int $count Default 30.
	 */
	$post_count = (int) apply_filters( 'beacon_llms_post_count', 30 );

	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => max( 0, $post_count ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'has_password'   => false,
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $posts ) ) {
		$lines[] = '';
		$lines[] = '## ' . __( 'Recent posts', 'beacon-ai-seo' );
		foreach ( $posts as $post ) {
			$lines[] = beacon_llms_list_item( $post );
		}
	}

	$lines[] = '';
	$lines[] = sprintf(
		/* translators: %s: ISO-8601 date the file was generated. */
		__( 'Generated %s by Beacon — AI & SEO.', 'beacon-ai-seo' ),
		gmdate( 'Y-m-d' )
	);
	$lines[] = '';

	$body = implode( "\n", $lines );

	/**
	 * Filter the fully assembled /llms.txt body before it is cached and served.
	 *
	 * @param string $body The Markdown document.
	 */
	return (string) apply_filters( 'beacon_llms_body', $body );
}

/**
 * Build a single Markdown list item for a post/page.
 *
 * Format: `- [Title](absolute-url): one-line excerpt`. The title and excerpt
 * are sanitized to single-line plain text and any Markdown-significant brackets
 * in the title are neutralized so the link never breaks.
 *
 * @param WP_Post $post The post or page.
 * @return string
 */
function beacon_llms_list_item( $post ) {
	$title = beacon_normalize_text( get_the_title( $post ) );
	$title = str_replace( array( '[', ']' ), array( '(', ')' ), $title );
	$url   = get_permalink( $post );

	$item = '- [' . $title . '](' . $url . ')';

	$summary = beacon_post_summary( $post, 25 );
	if ( '' !== $summary ) {
		$item .= ': ' . $summary;
	}

	return $item;
}

/**
 * Invalidate the cached /llms.txt body when content changes.
 *
 * Hooked on save_post (covers create, edit, trash, untrash, status changes).
 * Skips autosaves and revisions so a single edit triggers at most one clear.
 *
 * @param int $post_id The post ID being saved.
 * @return void
 */
function beacon_flush_llms_cache( $post_id ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	delete_transient( BEACON_LLMS_TRANSIENT );
}
add_action( 'save_post', 'beacon_flush_llms_cache' );
add_action( 'deleted_post', 'beacon_flush_llms_cache' );
