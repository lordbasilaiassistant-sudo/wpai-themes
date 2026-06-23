<?php
/**
 * 404 template.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="site-wrap">
	<section class="error-404">
		<p class="em-big" aria-hidden="true">404</p>
		<h1 class="entry-title"><?php esc_html_e( 'This page wandered off', 'emporium' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for is not here. Let us point you back to the good stuff.', 'emporium' ); ?></p>
		<p>
			<a class="em-btn" href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php echo esc_html( emporium_has_store() ? esc_html__( 'Back to the shop', 'emporium' ) : esc_html__( 'Back home', 'emporium' ) ); ?></a>
		</p>
		<div style="max-width:440px;margin:28px auto 0">
			<?php get_search_form(); ?>
		</div>
	</section>
</div>
<?php
get_footer();
