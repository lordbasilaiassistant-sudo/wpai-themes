<?php
/**
 * 404 template.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404" data-orbit-reveal>
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'Lost in orbit', 'orbit' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'This page drifted out of range. It may have moved, or it never launched. Try a search, or head back to base.', 'orbit' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<span class="cta-magnetic" data-orbit-magnetic>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'orbit' ); ?></a>
		</span>
	</p>
</section>
<?php
get_footer();
