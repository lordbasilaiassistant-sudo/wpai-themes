<?php
/**
 * Main template — the loop.
 *
 * On the blog home, a high-energy product hero leads, followed by count-up
 * metrics and a feature grid, then the newest post as a featured lead story
 * with the rest in a tidy card list. Other archives use the list form.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$orbit_is_blog_home = ( is_home() && ! is_paged() );
?>

<?php if ( $orbit_is_blog_home ) : ?>
	<?php
	orbit_render_hero();
	orbit_render_metrics();
	orbit_render_features();
	?>
<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header" data-orbit-reveal>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search: %s', 'orbit' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-orbit-reveal>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout" id="latest">
		<div class="layout__main">
			<?php if ( $orbit_is_blog_home ) : ?>
				<div class="posts-head" data-orbit-reveal>
					<div class="section-head" style="margin-bottom:0">
						<p class="section-head__kicker">// <?php esc_html_e( 'Changelog & writing', 'orbit' ); ?></p>
						<h2 class="section-head__title"><?php esc_html_e( 'Latest from the team', 'orbit' ); ?></h2>
					</div>
				</div>
			<?php endif; ?>

			<?php
			$orbit_index = 0;

			while ( have_posts() ) :
				the_post();

				// The newest post on the blog home becomes the featured lead story.
				$is_lead = ( $orbit_is_blog_home && 0 === $orbit_index );

				set_query_var( 'orbit_is_lead', $is_lead );
				get_template_part( 'template-parts/content', get_post_type() );

				$orbit_index++;
			endwhile;
			?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

	<?php
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => esc_html__( 'Newer', 'orbit' ),
		'next_text'          => esc_html__( 'Older', 'orbit' ),
		'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'orbit' ) . ' </span>',
	) );
	?>

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'orbit' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'It looks like nothing was found in this spot. Perhaps a search will help?', 'orbit' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
