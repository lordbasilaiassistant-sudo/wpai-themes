<?php
/**
 * Main template — the loop.
 *
 * On the portfolio home, a studio statement opens the page, then the work falls
 * into a large gallery grid: the newest piece becomes a full-width feature plate
 * and the rest tile beneath it. Other archives use the same grid without the
 * feature plate.
 *
 * @package Atelier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$atelier_is_home = ( is_home() && ! is_paged() );
?>

<?php if ( $atelier_is_home ) : ?>
	<section class="studio-intro" aria-label="<?php esc_attr_e( 'Studio statement', 'atelier' ); ?>" data-atelier-reveal>
		<div>
			<p class="studio-intro__kicker at-label"><?php esc_html_e( 'Selected work', 'atelier' ); ?></p>
			<h2 class="studio-intro__line" data-atelier-words>
				<span class="studio-intro__text">
					<?php
					$atelier_desc = get_bloginfo( 'description', 'display' );
					echo esc_html( $atelier_desc ? $atelier_desc : __( 'A studio for design, objects, and ideas.', 'atelier' ) );
					?>
				</span>
			</h2>
		</div>
		<p class="studio-intro__meta">
			<?php
			printf(
				/* translators: %s: site name. */
				esc_html__( 'A working portfolio from %s — projects, process, and the occasional note from the bench.', 'atelier' ),
				'<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
			);
			?>
		</p>
	</section>
<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header" data-atelier-reveal>
			<p class="page-header__kicker at-label"><?php esc_html_e( 'Search', 'atelier' ); ?></p>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for %s', 'atelier' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-atelier-reveal>
			<p class="page-header__kicker at-label"><?php esc_html_e( 'Archive', 'atelier' ); ?></p>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">
			<div class="project-grid">
				<?php
				$atelier_index = 0;

				while ( have_posts() ) :
					the_post();

					// The newest piece on the portfolio home becomes the feature plate.
					$is_feature = ( $atelier_is_home && 0 === $atelier_index );

					set_query_var( 'atelier_is_feature', $is_feature );
					set_query_var( 'atelier_index', $atelier_index );
					get_template_part( 'template-parts/content', get_post_type() );

					$atelier_index++;
				endwhile;
				?>
			</div><!-- .project-grid -->

			<?php
			the_posts_pagination( array(
				'mid_size'           => 1,
				'prev_text'          => esc_html__( 'Newer', 'atelier' ),
				'next_text'          => esc_html__( 'Older', 'atelier' ),
				'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'atelier' ) . ' </span>',
			) );
			?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing on the wall yet', 'atelier' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'There is no work here just now. Perhaps a search will turn something up?', 'atelier' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
