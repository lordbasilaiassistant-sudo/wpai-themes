<?php
/**
 * Layer 1 — write alt text at upload time.
 *
 * When a new image attachment is created with no alt text, derive a meaningful
 * value from its title, caption, or humanized filename and store it in the
 * standard `_wp_attachment_image_alt` meta. This means the alt is real, editable
 * data in the Media Library from the moment of upload — not a runtime patch.
 *
 * The cardinal rule: NEVER overwrite alt a human already wrote. We only ever set
 * alt when the field is empty, and we only do it once.
 *
 * @package DescribeAlt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Whether an attachment is an image we should describe.
 *
 * Non-image attachments (PDFs, audio, video) are skipped — alt text is an
 * <img> concern. Filterable so a site could opt other types in.
 *
 * @param int $attachment_id Attachment post ID.
 * @return bool
 */
function describe_alt_is_image( $attachment_id ) {
	$is_image = wp_attachment_is_image( (int) $attachment_id );

	/**
	 * Filter whether Describe treats an attachment as a describable image.
	 *
	 * @param bool $is_image      Whether WordPress reports this as an image.
	 * @param int  $attachment_id The attachment ID.
	 */
	return (bool) apply_filters( 'describe_alt_is_image', $is_image, $attachment_id );
}

/**
 * Fill empty alt on a freshly-added image attachment.
 *
 * Runs on `add_attachment` (fired once, when the attachment post is inserted).
 * Bails unless the item is an image with genuinely empty alt, so it can never
 * clobber a value a user typed in the upload dialog. The derived alt is
 * sanitized before storage.
 *
 * @param int $attachment_id The new attachment's post ID.
 * @return void
 */
function describe_alt_fill_on_upload( $attachment_id ) {
	$attachment_id = (int) $attachment_id;

	if ( ! describe_alt_is_image( $attachment_id ) ) {
		return;
	}

	// Respect any alt already present — never overwrite a human's words.
	if ( '' !== describe_alt_get_stored( $attachment_id ) ) {
		return;
	}

	$alt = describe_alt_derive( $attachment_id );

	if ( '' === $alt ) {
		return; // Nothing meaningful to say — leave it empty, don't invent noise.
	}

	update_post_meta(
		$attachment_id,
		'_wp_attachment_image_alt',
		sanitize_text_field( $alt )
	);

	// A new described image shifts coverage; let the stat recompute lazily.
	delete_transient( 'describe_alt_coverage' );
}
add_action( 'add_attachment', 'describe_alt_fill_on_upload' );

/**
 * Fill empty alt when an attachment's title or caption is edited.
 *
 * If a user uploads an image, skips alt, then later names it in the Media
 * Library, we take that as the cue to populate the still-empty alt from the new
 * title/caption. We still never overwrite a non-empty alt, so once a human has
 * written alt this is a no-op.
 *
 * @param int $attachment_id The attachment being updated.
 * @return void
 */
function describe_alt_fill_on_edit( $attachment_id ) {
	$attachment_id = (int) $attachment_id;

	if ( wp_is_post_revision( $attachment_id ) || wp_is_post_autosave( $attachment_id ) ) {
		return;
	}

	if ( 'attachment' !== get_post_type( $attachment_id ) || ! describe_alt_is_image( $attachment_id ) ) {
		return;
	}

	if ( '' !== describe_alt_get_stored( $attachment_id ) ) {
		return;
	}

	$alt = describe_alt_derive( $attachment_id );

	if ( '' === $alt ) {
		return;
	}

	update_post_meta(
		$attachment_id,
		'_wp_attachment_image_alt',
		sanitize_text_field( $alt )
	);

	delete_transient( 'describe_alt_coverage' );
}
add_action( 'edit_attachment', 'describe_alt_fill_on_edit' );
