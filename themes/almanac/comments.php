<?php
/**
 * Comments template — "marginalia".
 *
 * @package Almanac
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
			$almanac_count = get_comments_number();
			if ( '1' === $almanac_count ) {
				esc_html_e( 'One note in the margin', 'almanac' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s note in the margin', '%s notes in the margin', $almanac_count, 'comments title', 'almanac' ) ),
					esc_html( number_format_i18n( $almanac_count ) )
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
			'prev_text' => esc_html__( 'Older notes', 'almanac' ),
			'next_text' => esc_html__( 'Newer notes', 'almanac' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The margin is closed.', 'almanac' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Add a note in the margin', 'almanac' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'almanac' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
