<?php
/**
 * Post / page content partial.
 *
 * Renders in two modes:
 *   - dish      : the tactile menu-grid card used on the home and archives.
 *   - singular  : the full article on single posts and pages.
 *
 * @package Hearth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hearth_is_singular = is_singular();
$hearth_card_index  = (int) get_query_var( 'hearth_card_index' );
?>
<?php if ( ! $hearth_is_singular ) : // ----- dish card ----- ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry dish' ); ?> data-hearth-reveal>
		<?php hearth_dish_media( $hearth_card_index < 3 ); ?>
		<div class="dish__body">
			<?php
			if ( 'post' === get_post_type() ) {
				$hearth_cats = get_the_category();
				if ( ! empty( $hearth_cats ) ) {
					printf(
						'<p class="dish__cat">%s</p>',
						esc_html( $hearth_cats[0]->name )
					);
				}
			}
			?>
			<?php the_title( '<h3 class="dish__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
			<p class="dish__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<div class="dish__foot">
				<?php if ( 'post' === get_post_type() ) : ?>
					<span class="dish__date"><?php echo esc_html( get_the_date() ); ?></span>
				<?php else : ?>
					<span class="dish__date"><?php esc_html_e( 'Page', 'hearth' ); ?></span>
				<?php endif; ?>
				<a class="dish__more" href="<?php the_permalink(); ?>">
					<?php esc_html_e( 'Read', 'hearth' ); ?>
					<span class="dish__more-arrow" aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</div>
	</article>

<?php else : // ----- singular ----- ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--singular' ); ?>>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php hearth_category_pill(); ?>
					<?php hearth_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<?php hearth_featured_media( 'large', false ); ?>
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
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'hearth' ) . ' ',
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
		$hearth_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $hearth_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Filed under', 'hearth' ); ?></span>
				<?php echo wp_kses_post( $hearth_tags ); ?>
			</footer>
		<?php endif; ?>

	</article>

<?php endif; ?>
