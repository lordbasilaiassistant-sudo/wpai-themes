<?php
/**
 * Page template.
 *
 * Store host pages (Shop, Cart, Checkout, Wishlist) carry the commerce
 * shortcodes and want the full content width; ordinary pages (About, etc.)
 * read best as a centered article. We detect the difference and adapt.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$emporium_post    = get_post();
$emporium_content = $emporium_post ? $emporium_post->post_content : '';
$emporium_is_shop = (
	has_shortcode( $emporium_content, 'till_shop' ) ||
	has_shortcode( $emporium_content, 'till_cart' ) ||
	has_shortcode( $emporium_content, 'till_checkout' ) ||
	has_shortcode( $emporium_content, 'till_featured' ) ||
	has_shortcode( $emporium_content, 'keepsake_list' )
);
?>
<div class="site-wrap">
	<?php
	while ( have_posts() ) :
		the_post();

		if ( $emporium_is_shop ) :
			?>
			<div class="page-content">
				<header class="page-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>
				<?php the_content(); ?>
			</div>
			<?php
		else :
			?>
			<div class="layout layout--full">
				<div class="layout__main">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
						<header class="entry-header entry-header--single">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</header>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="entry-featured"><?php the_post_thumbnail( 'emporium-hero' ); ?></div>
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
					</article>

					<?php
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
					?>
				</div>
			</div>
			<?php
		endif;

	endwhile;
	?>
</div>
<?php
get_footer();
