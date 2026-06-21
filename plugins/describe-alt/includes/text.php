<?php
/**
 * Local alt-text derivation for Describe — Auto Alt Text.
 *
 * Pure, side-effect-free, network-free helpers. Everything here DERIVES a
 * human-readable alt string from data WordPress already has — the attachment
 * title, the caption, or a humanized version of the original filename. There is
 * no AI, no external service, and no HTTP call anywhere in this plugin: the
 * "describe" happens entirely from local metadata.
 *
 * Resolution order (strongest signal first):
 *   1. Attachment title  — what the author named the image in the Media Library.
 *   2. Caption           — the post_excerpt on the attachment.
 *   3. Humanized filename — the slug, cleaned into a readable phrase.
 *
 * @package DescribeAlt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Hard ceiling on a derived alt string's length, in characters.
 *
 * Long alt text is an accessibility anti-pattern (screen readers read it in
 * full), so derived values are trimmed on a word boundary to this length.
 * Author-written alt is never touched by this — only values we generate.
 */
const DESCRIBE_ALT_MAX_LENGTH = 120;

/**
 * Multibyte-safe string length.
 *
 * @param string $text Input string.
 * @return int Length in characters.
 */
function describe_alt_strlen( $text ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
}

/**
 * Multibyte-safe substring.
 *
 * @param string $text   Input string.
 * @param int    $start  Start offset.
 * @param int    $length Length (null for "to the end").
 * @return string
 */
function describe_alt_substr( $text, $start, $length = null ) {
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, $start, $length );
	}

	return null === $length ? substr( $text, $start ) : substr( $text, $start, $length );
}

/**
 * Whether a string looks like a real description rather than machine noise.
 *
 * Filenames such as "IMG_4821", "DSC00012", "20240114_103245", "Screenshot
 * 2024-01-14 at 10.32.45", a long hex/hash, or a bare number carry no meaning,
 * so we reject them and fall back to the next source. The test is deliberately
 * conservative: it only rejects strings that are *dominated* by digits/hashes,
 * so a legitimate name like "Route 66 diner" still passes.
 *
 * @param string $text Candidate phrase (already humanized).
 * @return bool True when the text is meaningful enough to use as alt.
 */
function describe_alt_is_meaningful( $text ) {
	$text = trim( (string) $text );

	if ( '' === $text ) {
		return false;
	}

	// Must contain at least one letter (any alphabet) — pure numbers/symbols out.
	if ( ! preg_match( '/\p{L}/u', $text ) ) {
		return false;
	}

	$compact = preg_replace( '/\s+/u', '', $text );
	$compact = (string) $compact;

	// A long unbroken hex run (md5/sha-style, ≥ 12 chars) is a hash, not a word.
	if ( preg_match( '/[0-9a-f]{12,}/i', $compact ) && ! preg_match( '/\s/u', $text ) ) {
		return false;
	}

	// Common camera/screenshot/export stems carry no descriptive value.
	$noise = array(
		'/^img[\s_-]*\d+$/iu',          // IMG_4821, IMG 4821.
		'/^dsc[\s_-]*\d+$/iu',          // DSC00012.
		'/^dscn[\s_-]*\d+$/iu',         // DSCN0001.
		'/^p\d{6,}$/iu',                // P1010101.
		'/^photo[\s_-]*\d+$/iu',        // photo 12, photo_3.
		'/^image[\s_-]*\d+$/iu',        // image 5, image-12.
		'/^untitled[\s_-]*\d*$/iu',     // untitled, untitled-1.
		'/^screen[\s_-]?shot.*$/iu',    // Screenshot 2024-01-14 at 10.32.45.
		'/^screen[\s_-]?capture.*$/iu', // Screen capture …
		'/^pasted[\s_-]image.*$/iu',    // pasted image 0.
		'/^download[\s_-]*\d*$/iu',     // download, download (1).
		'/^unnamed[\s_-]*\d*$/iu',      // unnamed, unnamed-2.
		'/^capture[\s_-]*\d*$/iu',      // Capture, Capture-3.
		'/^[a-z]{1,3}[\s_-]?\d{4,}$/iu',// generic short-prefix + long number.
	);

	foreach ( $noise as $pattern ) {
		if ( preg_match( $pattern, $text ) ) {
			return false;
		}
	}

	// Reject strings where digits overwhelm letters (e.g. "2024 01 14 1032").
	$letters = preg_match_all( '/\p{L}/u', $text );
	$digits  = preg_match_all( '/\p{N}/u', $text );
	if ( $digits > 0 && $letters <= $digits ) {
		return false;
	}

	return true;
}

/**
 * Turn a filename or slug into a clean, readable phrase.
 *
 * Pipeline (all local, no network):
 *   1. Drop any directory part and the file extension.
 *   2. Strip a trailing WordPress size suffix like "-1024x768" or a "-scaled"
 *      / "-rotated" / "-e1700000000000" (edited) marker.
 *   3. Strip a trailing "-2", "-3" de-duplication counter WordPress appends to
 *      colliding uploads.
 *   4. Replace "-" and "_" (and "+", ".") with spaces, decode %20 etc.
 *   5. Collapse runs of whitespace.
 *   6. Sentence-case the result (first letter up, the rest left as authored so
 *      acronyms like "PDF" or "iOS" survive).
 *
 * Returns '' when nothing readable remains. The caller decides whether the
 * result is meaningful (see describe_alt_is_meaningful).
 *
 * @param string $filename A filename, basename, or slug.
 * @return string Humanized phrase, or '' if empty after cleaning.
 */
function describe_alt_humanize_filename( $filename ) {
	$name = (string) $filename;

	// 1. Basename + decode any percent-encoding from a URL-derived name.
	$name = wp_basename( $name );
	$name = rawurldecode( $name );

	// 1b. Drop the extension (last dot segment) when present.
	$dot = strrpos( $name, '.' );
	if ( false !== $dot && $dot > 0 ) {
		$name = describe_alt_substr( $name, 0, $dot );
	}

	// 2. Strip WordPress-generated suffixes that describe the file, not the image.
	//    - size crop:  -1024x768, -150x150
	//    - scaled:     -scaled (big-image downscale)
	//    - rotated:    -rotated
	//    - edited:     -e1681234567890 (image editor revision)
	$name = preg_replace( '/-\d{1,5}x\d{1,5}$/', '', $name );
	$name = preg_replace( '/-(?:scaled|rotated)$/i', '', $name );
	$name = preg_replace( '/-e\d{10,}$/i', '', $name );

	// 3. Strip a trailing dedupe counter ("-2", "-3") WordPress adds on collision.
	//    Only when something readable precedes it, so "12" alone is left for the
	//    meaningfulness check to reject.
	$name = preg_replace( '/-\d{1,3}$/', '', $name );

	// 4. Separators → spaces.
	$name = str_replace( array( '-', '_', '+', '.' ), ' ', $name );

	// 5. Collapse whitespace.
	$name = preg_replace( '/\s+/u', ' ', $name );
	$name = trim( (string) $name );

	if ( '' === $name ) {
		return '';
	}

	// 6. Sentence-case: uppercase the first letter only; leave the rest intact so
	//    intentional casing (acronyms, brand names) is preserved.
	$first = describe_alt_substr( $name, 0, 1 );
	$rest  = describe_alt_substr( $name, 1 );

	if ( function_exists( 'mb_strtoupper' ) ) {
		$first = mb_strtoupper( $first );
	} else {
		$first = strtoupper( $first );
	}

	return $first . $rest;
}

/**
 * Clean an author-provided string (title or caption) into single-line alt text.
 *
 * Strips tags and shortcodes, decodes entities, and collapses whitespace so a
 * caption containing markup never leaks into the alt attribute.
 *
 * @param string $text Raw title or caption.
 * @return string Plain, single-line text (possibly empty).
 */
function describe_alt_clean( $text ) {
	$text = strip_shortcodes( (string) $text );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) ? get_bloginfo( 'charset' ) : 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Trim a derived phrase to DESCRIBE_ALT_MAX_LENGTH on a word boundary.
 *
 * No ellipsis is appended — for alt text a clean truncation reads better to a
 * screen reader than a trailing "…". Only ever applied to values we generate.
 *
 * @param string $text Plain text.
 * @return string
 */
function describe_alt_trim_length( $text ) {
	$text = (string) $text;

	if ( describe_alt_strlen( $text ) <= DESCRIBE_ALT_MAX_LENGTH ) {
		return $text;
	}

	$cut  = describe_alt_substr( $text, 0, DESCRIBE_ALT_MAX_LENGTH );
	$last = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );

	if ( false !== $last && $last > 0 ) {
		$cut = describe_alt_substr( $cut, 0, $last );
	}

	return rtrim( $cut, " \t\n\r\0\x0B.,;:-_" );
}

/**
 * Derive alt text for an attachment from its title, caption, then filename.
 *
 * This is the single source of truth used by BOTH layers (upload-time and
 * front-end backfill). It never reads or writes the existing alt — it only
 * computes what a good alt WOULD be from the other metadata — so callers can
 * decide whether to apply it.
 *
 * Each candidate is cleaned and length-trimmed, and must pass the
 * meaningfulness test before it's accepted; otherwise we fall through to the
 * next source. If nothing qualifies, returns '' (the caller leaves alt empty
 * rather than inventing noise).
 *
 * The final value is filterable via `describe_alt_text` so developers can
 * customize or override the derivation per attachment.
 *
 * @param int $attachment_id Attachment post ID.
 * @return string Derived alt text, or '' when nothing meaningful is available.
 */
function describe_alt_derive( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	$post          = get_post( $attachment_id );
	$derived       = '';

	if ( $post instanceof WP_Post ) {
		// 1. Attachment title.
		$candidate = describe_alt_trim_length( describe_alt_clean( $post->post_title ) );
		if ( describe_alt_is_meaningful( $candidate ) ) {
			$derived = $candidate;
		}

		// 2. Caption (post_excerpt).
		if ( '' === $derived ) {
			$candidate = describe_alt_trim_length( describe_alt_clean( $post->post_excerpt ) );
			if ( describe_alt_is_meaningful( $candidate ) ) {
				$derived = $candidate;
			}
		}

		// 3. Humanized filename — derived from the attached file path, falling
		//    back to the post slug, then the title (raw) as a last source.
		if ( '' === $derived ) {
			$file      = get_post_meta( $attachment_id, '_wp_attached_file', true );
			$source    = '' !== (string) $file ? (string) $file : $post->post_name;
			$candidate = describe_alt_trim_length( describe_alt_humanize_filename( $source ) );

			if ( describe_alt_is_meaningful( $candidate ) ) {
				$derived = $candidate;
			}
		}
	}

	/**
	 * Filter the alt text Describe derives for an attachment.
	 *
	 * Receives the locally-derived value (which may be '' when nothing
	 * meaningful was found) and the attachment ID. Return your own string to
	 * override, or '' to leave the image's alt untouched.
	 *
	 * @param string $derived       The derived alt text ('' if none).
	 * @param int    $attachment_id The attachment ID.
	 */
	$derived = (string) apply_filters( 'describe_alt_text', $derived, $attachment_id );

	return describe_alt_trim_length( describe_alt_clean( $derived ) );
}

/**
 * Read the stored alt text for an attachment, trimmed.
 *
 * @param int $attachment_id Attachment post ID.
 * @return string Stored alt ('' when none/whitespace-only).
 */
function describe_alt_get_stored( $attachment_id ) {
	$alt = get_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', true );

	return is_string( $alt ) ? trim( $alt ) : '';
}
