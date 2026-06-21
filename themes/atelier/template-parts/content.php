<?php
/**
 * Post / page content partial.
 *
 * Renders in three modes:
 *   - feature   : the large full-row feature plate at the top of the home grid.
 *   - card      : the standard project card used throughout the gallery grid.
 *   - singular  : the full article on single posts and pages.
 *
 * @package Atelier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$atelier_is_feature  = (bool) get_query_var( 'atelier_is_feature' );
$atelier_is_singular = is_singular();
$atelier_index       = (int) get_query_var( 'atelier_index' );
$atelier_frame       = str_pad( (string) ( $atelier_index + 1 ), 2, '0', STR_PAD_LEFT );

if ( $atelier_is_singular ) {
	$atelier_mode = 'singular';
} elseif ( $atelier_is_feature ) {
	$atelier_mode = 'feature';
} else {
	$atelier_mode = 'card';
}

// Feature plate and card share card styling; the feature adds a modifier.
$atelier_classes = 'entry entry--' . ( 'singular' === $atelier_mode ? 'singular' : 'card' );
if ( 'feature' === $atelier_mode ) {
	$atelier_classes .= ' entry--feature';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $atelier_classes ); ?> data-atelier-reveal>

	<?php if ( 'feature' === $atelier_mode ) : ?>

		<?php atelier_featured_media( 'atelier-feature', true, true, $atelier_frame ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php atelier_category_pill(); ?>
				<?php atelier_posted_meta(); ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'View project', 'atelier' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'card' === $atelier_mode ) : ?>

		<?php atelier_featured_media( 'large', true, false, $atelier_frame ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php atelier_category_pill(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php atelier_posted_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'View project', 'atelier' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php atelier_category_pill(); ?>
					<?php atelier_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php
			the_title(
				'<h1 class="entry-title" data-atelier-words><span class="entry-title__text">',
				'</span></h1>'
			);
			?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst" data-atelier-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div data-atelier-reveal>
				<?php atelier_featured_media( 'large', false ); ?>
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
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'atelier' ) . ' ',
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
		 * output (Kindred related projects) can span the full article width.
		 */
		do_action( 'wpai_entry_bottom' );
		?>

		<?php
		$atelier_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $atelier_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label at-label"><?php esc_html_e( 'Filed under', 'atelier' ); ?></span>
				<?php echo wp_kses_post( $atelier_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
