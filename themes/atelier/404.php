<?php
/**
 * 404 template.
 *
 * @package Atelier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404" data-atelier-reveal>
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'This piece has been moved', 'atelier' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'The page you were looking for is no longer on the wall, or perhaps never hung here. Try a search, or return to the studio.', 'atelier' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the studio', 'atelier' ); ?></a>
	</p>
</section>
<?php
get_footer();
