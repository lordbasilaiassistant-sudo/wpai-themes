<?php
/**
 * Data layer for Shipped — Auto Changelog & Roadmap.
 *
 * Resolves the watched category names, runs the tuned, cached WP_Query that
 * sources entries, detects a version tag (e.g. "v1.2.0") on a changelog post,
 * and buckets roadmap items into Planned / In progress / Shipped from a status
 * term. Everything here is side-effect-free except the transient read/write,
 * and the caches are flushed centrally on content changes (see the main file).
 *
 * @package Shipped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * The category name (or slug) whose posts become changelog timeline entries.
 *
 * Filterable so a site can point Shipped at a differently-named category
 * without touching code. Resolution accepts a name OR a slug (see
 * shipped_term_id_by_name_or_slug), so "Changelog", "changelog", and a custom
 * slug all work out of the box.
 *
 * @return string
 */
function shipped_changelog_category() {
	return (string) apply_filters( 'shipped_changelog_category', __( 'Changelog', 'shipped-changelog' ) );
}

/**
 * The category name (or slug) whose posts become roadmap board items.
 *
 * @return string
 */
function shipped_roadmap_category() {
	return (string) apply_filters( 'shipped_roadmap_category', __( 'Roadmap', 'shipped-changelog' ) );
}

/**
 * The roadmap status buckets, in board order.
 *
 * Each entry maps a canonical status key to the term slugs that put an item in
 * that column and the human label for the column heading. Items are matched by
 * the slug of any tag/term they carry, so authors just tag a roadmap post with
 * `planned`, `in-progress`, or `shipped` (also `done`/`complete` aliases).
 *
 * Filterable so themes/sites can rename columns or add accepted slugs.
 *
 * @return array<string,array{label:string,slugs:string[]}>
 */
function shipped_roadmap_statuses() {
	$statuses = array(
		'planned'     => array(
			'label' => __( 'Planned', 'shipped-changelog' ),
			'slugs' => array( 'planned', 'plan', 'backlog', 'idea', 'considering' ),
		),
		'in-progress' => array(
			'label' => __( 'In progress', 'shipped-changelog' ),
			'slugs' => array( 'in-progress', 'in progress', 'inprogress', 'progress', 'doing', 'building', 'wip' ),
		),
		'shipped'     => array(
			'label' => __( 'Shipped', 'shipped-changelog' ),
			'slugs' => array( 'shipped', 'ship', 'done', 'complete', 'completed', 'released', 'live' ),
		),
	);

	/**
	 * Filter the roadmap status columns and their accepted term slugs.
	 *
	 * @param array $statuses The default status map.
	 */
	return (array) apply_filters( 'shipped_roadmap_statuses', $statuses );
}

/**
 * Find a term ID in a taxonomy by its name OR slug (case-insensitive on name).
 *
 * Lets the category filters accept either form. Returns 0 when nothing matches
 * so callers can bail early without a query against a non-existent term.
 *
 * @param string $name_or_slug Category/term name or slug.
 * @param string $taxonomy     Taxonomy to search (default 'category').
 * @return int Term ID, or 0 if not found.
 */
function shipped_term_id_by_name_or_slug( $name_or_slug, $taxonomy = 'category' ) {
	$name_or_slug = trim( (string) $name_or_slug );

	if ( '' === $name_or_slug ) {
		return 0;
	}

	$term = get_term_by( 'slug', sanitize_title( $name_or_slug ), $taxonomy );

	if ( ! $term instanceof WP_Term ) {
		$term = get_term_by( 'name', $name_or_slug, $taxonomy );
	}

	return $term instanceof WP_Term ? (int) $term->term_id : 0;
}

/**
 * The transient key for a watched category's cached entry IDs.
 *
 * The category slug and the plugin version are folded into the key so changing
 * the category (via filter) or shipping a new version never serves stale data.
 *
 * @param string $kind 'changelog' or 'roadmap'.
 * @param int    $term_id The resolved category term ID.
 * @return string Transient name.
 */
function shipped_cache_key( $kind, $term_id ) {
	return 'shipped_' . sanitize_key( $kind ) . '_' . (int) $term_id . '_' . SHIPPED_VERSION;
}

/**
 * Delete all of this plugin's transient caches.
 *
 * Called on content/term changes. We clear by the known key shape rather than a
 * broad LIKE delete: the category can change via filter, so we resolve the
 * current changelog/roadmap term IDs and drop their keys. As a backstop the
 * 12-hour TTL expires anything orphaned by a mid-flight filter change.
 *
 * @return void
 */
function shipped_flush_caches() {
	$changelog_id = shipped_term_id_by_name_or_slug( shipped_changelog_category() );
	$roadmap_id   = shipped_term_id_by_name_or_slug( shipped_roadmap_category() );

	if ( $changelog_id ) {
		delete_transient( shipped_cache_key( 'changelog', $changelog_id ) );
	}
	if ( $roadmap_id ) {
		delete_transient( shipped_cache_key( 'roadmap', $roadmap_id ) );
	}
}

/**
 * Maximum number of entries pulled per board/timeline.
 *
 * A generous cap that keeps a runaway query bounded while comfortably covering
 * real-world changelogs and roadmaps. Filterable.
 *
 * @return int Always >= 1.
 */
function shipped_max_entries() {
	$max = (int) apply_filters( 'shipped_max_entries', 200 );

	return $max > 0 ? $max : 200;
}

/**
 * Fetch the published post IDs in a watched category, newest first, cached.
 *
 * Tuned for speed: IDs only (`fields => 'ids'`), `no_found_rows`, and no meta
 * cache priming. Term cache priming is left ON because the renderer needs each
 * post's tags (version / status) right after — priming once here is cheaper
 * than N lazy lookups. The ID list is cached in a transient and invalidated on
 * content changes.
 *
 * @param string $kind     'changelog' or 'roadmap' (selects the category + key).
 * @param string $category The category name/slug to source from.
 * @return int[] Ordered post IDs (may be empty).
 */
function shipped_get_entry_ids( $kind, $category ) {
	$term_id = shipped_term_id_by_name_or_slug( $category );

	if ( ! $term_id ) {
		return array();
	}

	$key    = shipped_cache_key( $kind, $term_id );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return array_map( 'intval', $cached );
	}
	if ( 'none' === $cached ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => shipped_max_entries(),
			'ignore_sticky_posts'    => true,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true, // Renderer reads tags right after.
			'cat'                    => $term_id,
		)
	);

	$ids = array_map( 'intval', $query->posts );

	set_transient( $key, empty( $ids ) ? 'none' : $ids, SHIPPED_CACHE_TTL );

	return $ids;
}

/**
 * Detect a version label on a post from a tag shaped like "v1.2.0" or "1.2.0".
 *
 * Scans the post's tags for the first that looks like a (optionally
 * v-prefixed) dotted version number and returns it normalized to a leading "v"
 * (e.g. "1.2" → "v1.2"). Returns '' when no version-like tag is present.
 *
 * @param int $post_id The changelog post.
 * @return string Normalized version label (e.g. "v1.2.0"), or ''.
 */
function shipped_post_version( $post_id ) {
	$tags = get_the_tags( (int) $post_id );

	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return '';
	}

	foreach ( $tags as $tag ) {
		$name = trim( (string) $tag->name );

		// Match v1, v1.2, v1.2.3, optionally with a -beta.1 style suffix, or the
		// same without the leading "v". Requires at least one dot to avoid
		// catching a bare "1" or year-like tag.
		if ( preg_match( '/^v?(\d+(?:\.\d+)+(?:[-.][0-9a-z]+)*)$/i', $name, $m ) ) {
			return 'v' . $m[1];
		}
	}

	return '';
}

/**
 * Resolve which roadmap column a post belongs to from its tags/terms.
 *
 * Compares the slugs of every tag and category on the post against each
 * status's accepted slug list (see shipped_roadmap_statuses). The first
 * matching column wins, in board order. Posts with no recognized status fall
 * back to the first column (Planned) so nothing is silently dropped.
 *
 * @param int   $post_id  The roadmap post.
 * @param array $statuses The status map (passed in to avoid re-filtering per post).
 * @return string The canonical status key (e.g. 'in-progress').
 */
function shipped_post_status_key( $post_id, $statuses ) {
	$post_id   = (int) $post_id;
	$own_slugs = array();

	foreach ( array( 'post_tag', 'category' ) as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$own_slugs[] = $term->slug;
				// Also fold the name to a slug so "In Progress" matches "in-progress".
				$own_slugs[] = sanitize_title( $term->name );
			}
		}
	}

	$own_slugs = array_unique( $own_slugs );

	foreach ( $statuses as $key => $status ) {
		$accepted = array();
		foreach ( (array) $status['slugs'] as $slug ) {
			$accepted[] = sanitize_title( $slug );
		}

		if ( array_intersect( $own_slugs, $accepted ) ) {
			return (string) $key;
		}
	}

	// Default to the first column so untagged items still surface.
	$keys = array_keys( $statuses );

	return isset( $keys[0] ) ? (string) $keys[0] : 'planned';
}
