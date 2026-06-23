<?php
/**
 * Single post / product template.
 *
 * For products, Till — Commerce injects the full product layout (gallery,
 * price, add-to-cart, related) into the_content, so this template gives it a
 * clean, full-width container with a breadcrumb. For journal posts it renders
 * a centered article with a featured image.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$emporium_is_product = ( 'product' === get_post_type() );
?>
<div class="site-wrap">
	<?php if ( $emporium_is_product ) : ?>

		<div class="product-single">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<nav class="em-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'emporium' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'emporium' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'emporium' ); ?></a>
					<span aria-hidden="true">/</span>
					<?php the_title(); ?>
				</nav>
				<?php the_content(); ?>
			<?php endwhile; ?>
		</div>

	<?php else : ?>

		<div class="layout layout--full">
			<div class="layout__main">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
						<header class="entry-header entry-header--single">
							<?php
							$emporium_cats = get_the_category();
							if ( ! empty( $emporium_cats ) ) :
								?>
								<a class="entry-cat" href="<?php echo esc_url( get_category_link( $emporium_cats[0]->term_id ) ); ?>"><?php echo esc_html( $emporium_cats[0]->name ); ?></a>
							<?php endif; ?>
							<h1 class="entry-title"><?php the_title(); ?></h1>
							<div class="entry-meta">
								<span><?php echo esc_html( get_the_date() ); ?></span>
								<span aria-hidden="true">&middot;</span>
								<span><?php printf( esc_html__( 'by %s', 'emporium' ), esc_html( get_the_author() ) ); ?></span>
							</div>
						</header>

						<?php if ( has_post_thumbnail() ) : ?>
							<div class="entry-featured"><?php the_post_thumbnail( 'emporium-hero', array( 'fetchpriority' => 'high' ) ); ?></div>
						<?php endif; ?>

						<div class="entry-content">
							<?php
							the_content();
							wp_link_pages( array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'emporium' ),
								'after'  => '</div>',
							) );
							?>
						</div>

						<?php if ( has_tag() ) : ?>
							<footer class="entry-footer"><?php the_tags( esc_html__( 'Tagged: ', 'emporium' ), ', ' ); ?></footer>
						<?php endif; ?>
					</article>

					<?php
					the_post_navigation( array(
						'prev_text' => '<span class="post-nav__label">' . esc_html__( 'Previous', 'emporium' ) . '</span><span class="post-nav__title">%title</span>',
						'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next', 'emporium' ) . '</span><span class="post-nav__title">%title</span>',
					) );

					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile;
				?>
			</div>
		</div>

	<?php endif; ?>
</div>
<?php
get_footer();
