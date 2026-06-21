<?php
/**
 * Post / page content partial.
 *
 * Renders in four modes (set via the `dispatch_mode` query var):
 *   - lead      : the large hero story at the top of the blog home.
 *   - secondary : a compact horizontal story in the hero column.
 *   - list      : the card used in the two-up "More stories" river.
 *   - singular  : the full article on single posts and pages.
 *
 * @package Dispatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dispatch_is_singular = is_singular();
$dispatch_mode        = $dispatch_is_singular ? 'singular' : ( get_query_var( 'dispatch_mode' ) ? get_query_var( 'dispatch_mode' ) : 'list' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $dispatch_mode ); ?> data-dispatch-reveal>

	<?php if ( 'lead' === $dispatch_mode ) : ?>

		<?php dispatch_featured_media( 'dispatch-lead', true, true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php dispatch_category_tag(); ?>
				<?php dispatch_posted_meta(); ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Full story', 'dispatch' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'secondary' === $dispatch_mode ) : ?>

		<?php dispatch_featured_media( 'medium', true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php dispatch_category_tag(); ?>
			</div>
			<?php the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
		</div>

	<?php elseif ( 'list' === $dispatch_mode ) : ?>

		<?php dispatch_featured_media( 'medium_large', true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php dispatch_category_tag(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php dispatch_posted_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading', 'dispatch' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php dispatch_category_tag(); ?>
					<?php dispatch_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<?php dispatch_featured_media( 'large', false ); ?>
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

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'dispatch' ) . ' ',
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
		 * output (Kindred related posts) can span the full article width.
		 */
		do_action( 'wpai_entry_bottom' );
		?>

		<?php
		$dispatch_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $dispatch_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Filed under', 'dispatch' ); ?></span>
				<?php echo wp_kses_post( $dispatch_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
