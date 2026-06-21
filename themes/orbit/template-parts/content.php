<?php
/**
 * Post / page content partial.
 *
 * Renders in three modes:
 *   - lead      : the large featured story at the top of the blog home.
 *   - list      : the compact card used for every other archive post.
 *   - singular  : the full article on single posts and pages.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$orbit_is_lead     = (bool) get_query_var( 'orbit_is_lead' );
$orbit_is_singular = is_singular();
$orbit_mode        = $orbit_is_singular ? 'singular' : ( $orbit_is_lead ? 'lead' : 'list' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $orbit_mode ); ?> data-orbit-reveal>

	<?php if ( 'lead' === $orbit_mode ) : ?>

		<?php orbit_featured_media( 'orbit-lead', true, true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php orbit_category_pill(); ?>
				<?php orbit_posted_meta(); ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Read the story', 'orbit' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'list' === $orbit_mode ) : ?>

		<?php orbit_featured_media( 'medium_large', true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php orbit_category_pill(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php orbit_posted_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading', 'orbit' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php orbit_category_pill(); ?>
					<?php orbit_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst" data-orbit-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div data-orbit-reveal>
				<?php orbit_featured_media( 'large', false ); ?>
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

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'orbit' ) . ' ',
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
		$orbit_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $orbit_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Filed under', 'orbit' ); ?></span>
				<?php echo wp_kses_post( $orbit_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
