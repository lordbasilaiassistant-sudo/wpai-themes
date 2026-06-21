<?php
/**
 * 404 template.
 *
 * @package Almanac
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404" data-alm-reveal>
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'This thread leads nowhere', 'almanac' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'The note you followed has been uprooted, or perhaps was never planted. Try a search, or wander back into the garden.', 'almanac' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the garden', 'almanac' ); ?></a>
	</p>
</section>
<?php
get_footer();
