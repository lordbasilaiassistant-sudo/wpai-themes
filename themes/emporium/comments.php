<?php
/**
 * Comments template.
 *
 * @package Emporium
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
			$emporium_count = get_comments_number();
			if ( 1 === (int) $emporium_count ) {
				esc_html_e( 'One comment', 'emporium' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s comment', '%s comments', $emporium_count, 'comments title', 'emporium' ) ),
					esc_html( number_format_i18n( $emporium_count ) )
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
			'prev_text' => esc_html__( 'Older comments', 'emporium' ),
			'next_text' => esc_html__( 'Newer comments', 'emporium' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'emporium' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'    => esc_html__( 'Leave a comment', 'emporium' ),
		'title_reply_to' => esc_html__( 'Reply to %s', 'emporium' ),
		'class_submit'   => 'em-btn comment-submit',
	) );
	?>
</section>
