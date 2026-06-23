<?php
/**
 * Archive template.
 *
 * Product archives and product-category archives render as a shop grid using
 * Till's product cards; all other archives use the journal list layout.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$emporium_is_shop_archive = ( is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) ) && function_exists( 'till_product_card' );

if ( ! $emporium_is_shop_archive ) {
	// Fall back to the journal list for category, tag, date, author archives.
	get_template_part( 'index' );
	return;
}

get_header();
?>
<div class="site-wrap">
	<header class="page-header">
		<p class="em-label"><?php esc_html_e( 'Shop', 'emporium' ); ?></p>
		<?php
		if ( is_tax( 'product_cat' ) ) {
			single_term_title( '<h1 class="page-header__title">', '</h1>' );
			the_archive_description( '<div class="page-header__desc">', '</div>' );
		} else {
			echo '<h1 class="page-header__title">' . esc_html__( 'All products', 'emporium' ) . '</h1>';
		}
		?>
	</header>

	<div class="page-content">
		<?php if ( have_posts() ) : ?>
			<div class="till-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					echo till_product_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Till.
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => esc_html__( '&larr; Prev', 'emporium' ),
				'next_text' => esc_html__( 'Next &rarr;', 'emporium' ),
			) );
			?>
		<?php else : ?>
			<p class="till-empty"><?php esc_html_e( 'No products here yet.', 'emporium' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
