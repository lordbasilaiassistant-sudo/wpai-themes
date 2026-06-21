<?php
/**
 * 404 template.
 *
 * @package Hearth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404" data-hearth-reveal>
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'This table is not set', 'hearth' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'The page you were looking for has been cleared away, or perhaps never made it to the menu. Try a search, or head back to the front door.', 'hearth' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'hearth' ); ?></a>
	</p>
</section>
<?php
get_footer();
