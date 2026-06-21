<?php
/**
 * Main template — the loop.
 *
 * On the home page, the newest post anchors a welcoming hero (with the live
 * "today's hours" card), and the remaining posts plate up into a tactile
 * menu/offerings grid. Other archives use the same grid without the hero.
 *
 * @package Hearth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hearth_is_home = ( is_home() && ! is_paged() );
?>

<?php if ( have_posts() ) : ?>

	<?php
	// --- Home hero: lead the page with the newest story + hours card --------
	if ( $hearth_is_home ) :
		the_post();
		$hearth_has_more = have_posts();
		?>
		<section class="hero" aria-label="<?php esc_attr_e( 'Welcome', 'hearth' ); ?>">
			<div class="hero__inner">
				<div class="hero__copy" data-hearth-reveal>
					<p class="hero__kicker"><?php esc_html_e( 'Pull up a chair', 'hearth' ); ?></p>
					<?php
					$hearth_tagline = get_bloginfo( 'description', 'display' );
					if ( $hearth_tagline ) :
						?>
						<h2 class="hero__title"><?php echo esc_html( $hearth_tagline ); ?></h2>
					<?php else : ?>
						<h2 class="hero__title"><?php esc_html_e( 'Warm food, made by hand, in good company.', 'hearth' ); ?></h2>
					<?php endif; ?>
					<p class="hero__lead">
						<?php
						$hearth_intro = get_the_excerpt();
						echo esc_html( $hearth_intro ? $hearth_intro : __( 'A neighborhood table where the coffee is hot, the bread is fresh, and you are always welcome to stay a while.', 'hearth' ) );
						?>
					</p>
					<div class="hero__actions">
						<a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( "See what's cooking", 'hearth' ); ?></a>
						<?php
						$hearth_about = get_page_by_path( 'about' );
						if ( $hearth_about ) :
							?>
							<a class="button button--ghost" href="<?php echo esc_url( get_permalink( $hearth_about ) ); ?>"><?php esc_html_e( 'Our story', 'hearth' ); ?></a>
						<?php endif; ?>
					</div>
				</div>

				<div class="hero__feature" data-hearth-reveal>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="hero__media has-image">
							<?php
							the_post_thumbnail(
								'hearth-hero',
								array(
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
								)
							);
							?>
						</div>
					<?php else : ?>
						<div class="hero__media is-placeholder" aria-hidden="true">
							<span class="hero__media-glyph">&#9749;</span>
						</div>
					<?php endif; ?>
				</div>

				<?php hearth_hours_card( true ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// --- Archive header (search / category / etc.) --------------------------
	if ( is_search() ) :
		?>
		<header class="page-header" data-hearth-reveal>
			<p class="page-header__eyebrow"><?php esc_html_e( 'Search', 'hearth' ); ?></p>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for %s', 'hearth' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-hearth-reveal>
			<p class="page-header__eyebrow"><?php esc_html_e( 'From the kitchen', 'hearth' ); ?></p>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">

			<?php if ( $hearth_is_home && isset( $hearth_has_more ) && $hearth_has_more ) : ?>
				<div class="section__head" data-hearth-reveal>
					<div>
						<p class="section__eyebrow"><?php esc_html_e( 'On the menu', 'hearth' ); ?></p>
						<h2 class="section__title"><?php esc_html_e( 'Fresh from the kitchen', 'hearth' ); ?></h2>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : // Skip the grid entirely when the home hero consumed the only post. ?>
				<div class="menu-grid">
					<?php
					$hearth_index = 0;

					while ( have_posts() ) :
						the_post();

						set_query_var( 'hearth_card_index', $hearth_index );
						get_template_part( 'template-parts/content', get_post_type() );

						$hearth_index++;
					endwhile;
					?>
				</div><!-- .menu-grid -->
			<?php endif; ?>

			<?php
			the_posts_pagination( array(
				'mid_size'           => 1,
				'prev_text'          => esc_html__( 'Newer', 'hearth' ),
				'next_text'          => esc_html__( 'Older', 'hearth' ),
				'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'hearth' ) . ' </span>',
			) );
			?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'The kitchen is quiet right now', 'hearth' ); ?></h2>
				<p><?php esc_html_e( 'Nothing has been served here yet. Try a search, or come back soon.', 'hearth' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
