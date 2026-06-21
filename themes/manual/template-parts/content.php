<?php
/**
 * Post / page content partial.
 *
 * Renders in three modes:
 *   - lead      : the large featured doc at the top of the docs home.
 *   - card      : the compact card used in the doc grid and on archives.
 *   - singular  : the full article on single docs/posts and pages, with the
 *                 signature "On this page" rail mount and the WPAI companion
 *                 hooks fired around the article body.
 *
 * @package Manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$manual_is_singular = is_singular();
$manual_card_mode   = (string) get_query_var( 'manual_card_mode' );

if ( $manual_is_singular ) {
	$manual_mode = 'singular';
} elseif ( 'lead' === $manual_card_mode ) {
	$manual_mode = 'lead';
} else {
	$manual_mode = 'card';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $manual_mode ); ?> data-manual-reveal>

	<?php if ( 'lead' === $manual_mode ) : ?>

		<?php manual_featured_media( 'manual-lead', true, true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php manual_category_pill(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php manual_posted_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Read the guide', 'manual' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'card' === $manual_mode ) : ?>

		<div class="entry-meta">
			<?php manual_category_pill(); ?>
			<?php if ( 'post' === get_post_type() ) : ?>
				<?php manual_posted_meta(); ?>
			<?php endif; ?>
		</div>
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div>
		<a class="read-more" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Open', 'manual' ); ?>
			<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
		</a>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php manual_category_pill(); ?>
					<?php manual_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst" data-manual-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div data-manual-reveal>
				<?php manual_featured_media( 'large', false ); ?>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Native WPAI companion slot — top of the article body.
		 *
		 * Fires right after the entry header (meta + title + featured image) and
		 * immediately before the content, OUTSIDE the .entry-content wrapper so
		 * hooked output (reading-time badge, Contents box) can sit at full article
		 * width rather than being constrained to the prose measure. Gated behind
		 * add_theme_support( 'wpai-companions' ); companions self-guard to single
		 * posts, so firing on pages is harmless.
		 */
		do_action( 'wpai_entry_top' );
		?>

		<?php
		/**
		 * Signature mount point — Manual's own "On this page" rail.
		 *
		 * motion.js replaces this element with a navigation rail built from the
		 * article's headings, with active-section tracking and smooth scrolling.
		 * It is removed entirely if there are fewer than two headings, and never
		 * appears at all without JS, so the article reads cleanly either way.
		 */
		?>
		<div data-manual-toc data-manual-toc-label="<?php esc_attr_e( 'On this page', 'manual' ); ?>" hidden></div>

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'manual' ) . ' ',
				'after'  => '</div>',
			) );
			?>
		</div>

		<?php
		/**
		 * Native WPAI companion slot — bottom of the article body.
		 *
		 * Fires immediately after the content/wp_link_pages and before the entry
		 * footer (tags) and comments, OUTSIDE the .entry-content wrapper so hooked
		 * output (Kindred related docs) can span the full article width.
		 */
		do_action( 'wpai_entry_bottom' );
		?>

		<?php
		$manual_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $manual_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Tagged', 'manual' ); ?></span>
				<?php echo wp_kses_post( $manual_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
