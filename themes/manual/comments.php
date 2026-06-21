<?php
/**
 * Comments template.
 *
 * @package Manual
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
			$manual_count = get_comments_number();
			if ( '1' === $manual_count ) {
				esc_html_e( 'One note', 'manual' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s note', '%s notes', $manual_count, 'comments title', 'manual' ) ),
					esc_html( number_format_i18n( $manual_count ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 44,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation( array(
			'prev_text' => esc_html__( 'Older notes', 'manual' ),
			'next_text' => esc_html__( 'Newer notes', 'manual' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The discussion is closed.', 'manual' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Add a note', 'manual' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'manual' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
