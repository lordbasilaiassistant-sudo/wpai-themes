<?php
/**
 * Main template — the loop.
 *
 * The docs home opens with a documentation hero (prominent search + version
 * context), then a featured lead doc and a tidy two-up grid of doc cards, all
 * beside the sticky left-hand docs navigation rail. Other archives use the grid.
 *
 * @package Manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$manual_is_docs_home = ( is_home() && ! is_paged() );
?>

<?php if ( $manual_is_docs_home ) : ?>
	<section class="docs-hero" data-manual-reveal>
		<p class="docs-hero__kicker"><?php esc_html_e( 'Documentation', 'manual' ); ?></p>
		<h2 class="docs-hero__title">
			<?php
			$manual_desc = get_bloginfo( 'description', 'display' );
			echo esc_html( $manual_desc ? $manual_desc : get_bloginfo( 'name' ) );
			?>
		</h2>
		<?php if ( $manual_desc ) : ?>
			<p class="docs-hero__desc"><?php esc_html_e( 'Everything you need to get started, in one calm, searchable place. Browse the sections on the left, or jump straight in.', 'manual' ); ?></p>
		<?php endif; ?>
		<div class="docs-hero__search">
			<?php get_search_form(); ?>
		</div>
		<p class="docs-hero__meta">
			<?php
			printf(
				/* translators: %s: keyboard shortcut hint. */
				esc_html__( 'Tip: press %s, then start typing to search.', 'manual' ),
				'<kbd>/</kbd>'
			);
			?>
		</p>
	</section>
<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header" data-manual-reveal>
			<p class="page-header__eyebrow"><?php esc_html_e( 'Search results', 'manual' ); ?></p>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for %s', 'manual' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-manual-reveal>
			<p class="page-header__eyebrow"><?php esc_html_e( 'Section', 'manual' ); ?></p>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<?php get_sidebar(); ?>

		<div class="layout__main">
			<?php
			$manual_index = 0;
			$manual_grid_open = false;

			while ( have_posts() ) :
				the_post();

				// On the docs home, the newest post becomes the featured lead doc;
				// the rest fall into a two-up card grid.
				$is_lead = ( $manual_is_docs_home && 0 === $manual_index );

				if ( $is_lead ) {
					set_query_var( 'manual_card_mode', 'lead' );
					get_template_part( 'template-parts/content', get_post_type() );
				} else {
					if ( ! $manual_grid_open ) {
						echo '<div class="docs-grid">';
						$manual_grid_open = true;
					}
					set_query_var( 'manual_card_mode', 'card' );
					get_template_part( 'template-parts/content', get_post_type() );
				}

				$manual_index++;
			endwhile;

			if ( $manual_grid_open ) {
				echo '</div><!-- .docs-grid -->';
			}
			?>
		</div><!-- .layout__main -->
	</div><!-- .layout -->

	<?php
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => esc_html__( 'Newer', 'manual' ),
		'next_text'          => esc_html__( 'Older', 'manual' ),
		'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'manual' ) . ' </span>',
	) );
	?>

<?php else : ?>

	<div class="layout">
		<?php get_sidebar(); ?>
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'manual' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'There is no documentation in this spot yet. Try a search instead.', 'manual' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
	</div>

<?php endif; ?>

<?php
get_footer();
