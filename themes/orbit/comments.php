<?php
/**
 * Comments template.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$orbit_count = get_comments_number();
			if ( '1' === $orbit_count ) {
				esc_html_e( 'One response', 'orbit' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s response', '%s responses', $orbit_count, 'comments title', 'orbit' ) ),
					esc_html( number_format_i18n( $orbit_count ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation( array(
			'prev_text' => esc_html__( 'Older comments', 'orbit' ),
			'next_text' => esc_html__( 'Newer comments', 'orbit' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The conversation is closed.', 'orbit' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Leave a response', 'orbit' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'orbit' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
