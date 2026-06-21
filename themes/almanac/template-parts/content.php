<?php
/**
 * Post / page content partial.
 *
 * Renders in three modes:
 *   - lead      : the featured "seedling" (freshest note) at the top of home.
 *   - list      : the compact "note card" used for every other archive post.
 *   - singular  : the full note on single posts and pages.
 *
 * @package Almanac
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$almanac_is_lead     = (bool) get_query_var( 'almanac_is_lead' );
$almanac_is_singular = is_singular();
$almanac_mode        = $almanac_is_singular ? 'singular' : ( $almanac_is_lead ? 'lead' : 'list' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $almanac_mode ); ?> data-alm-reveal>

	<?php if ( 'lead' === $almanac_mode ) : ?>

		<?php almanac_featured_media( 'almanac-lead', true, true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php almanac_category_pill(); ?>
				<?php almanac_tended_meta(); ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<?php almanac_tag_row(); ?>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Read the note', 'almanac' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'list' === $almanac_mode ) : ?>

		<?php almanac_featured_media( 'medium_large', true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php almanac_category_pill(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php almanac_tended_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<?php almanac_tag_row( 3 ); ?>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue', 'almanac' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php almanac_category_pill(); ?>
				</div>
			<?php endif; ?>

			<?php
			the_title(
				'<h1 class="entry-title" data-alm-words><span class="entry-title__text">',
				'</span></h1>'
			);
			almanac_sprout();
			?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst" data-alm-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-evergreen">
					<?php almanac_tended_meta(); ?>
					<span class="entry-meta__sep" aria-hidden="true">&middot;</span>
					<span class="tended"><?php
						printf(
							/* translators: %s: post author. */
							esc_html__( 'Kept by %s', 'almanac' ),
							'<span class="author">' . esc_html( get_the_author() ) . '</span>'
						);
					?></span>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div data-alm-reveal>
				<?php almanac_featured_media( 'large', false ); ?>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Native WPAI companion slot — top of the article body.
		 *
		 * Fires right after the entry header (meta + title + evergreen stamps +
		 * featured image) and immediately before the content, OUTSIDE the
		 * .entry-content wrapper so hooked output (reading-time badge, Contents
		 * box) can sit at full article width rather than being constrained to
		 * the prose measure. Gated behind add_theme_support( 'wpai-companions' );
		 * companions self-guard to single posts, so firing on pages is harmless.
		 */
		do_action( 'wpai_entry_top' );
		?>

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'almanac' ) . ' ',
				'after'  => '</div>',
			) );
			?>
		</div>

		<?php
		/**
		 * Native WPAI companion slot — bottom of the article body.
		 *
		 * Fires immediately after the content/wp_link_pages and before the entry
		 * footer (tags) and comments, OUTSIDE the .entry-content wrapper so
		 * hooked output (Kindred related notes) can span the full article width.
		 */
		do_action( 'wpai_entry_bottom' );
		?>

		<?php
		$almanac_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $almanac_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Threads', 'almanac' ); ?></span>
				<?php echo wp_kses_post( $almanac_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
