<?php
/**
 * Main template — the journal (blog) loop and archives.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="site-wrap">
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<header class="page-header">
			<p class="em-label"><?php esc_html_e( 'The Journal', 'emporium' ); ?></p>
			<h1 class="page-header__title"><?php single_post_title(); ?></h1>
		</header>
	<?php elseif ( is_search() ) : ?>
		<header class="page-header">
			<p class="em-label"><?php esc_html_e( 'Search', 'emporium' ); ?></p>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for %s', 'emporium' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header">
			<p class="em-label"><?php esc_html_e( 'Archive', 'emporium' ); ?></p>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">
			<?php if ( have_posts() ) : ?>
				<div class="post-list">
					<?php
					while ( have_posts() ) :
						the_post();
						emporium_post_card();
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination( array(
					'mid_size'  => 1,
					'prev_text' => esc_html__( 'Newer', 'emporium' ),
					'next_text' => esc_html__( 'Older', 'emporium' ),
				) );
				?>
			<?php else : ?>
				<article class="entry entry--empty">
					<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'emporium' ); ?></h2>
					<p><?php esc_html_e( 'We could not find anything to show. Try a search?', 'emporium' ); ?></p>
					<?php get_search_form(); ?>
				</article>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
