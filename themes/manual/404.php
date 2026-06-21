<?php
/**
 * 404 template.
 *
 * @package Manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404" data-manual-reveal>
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'Page not found', 'manual' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'That page has moved or never existed. Try a search, or head back to the documentation home.', 'manual' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the docs', 'manual' ); ?></a>
	</p>
</section>
<?php
get_footer();
