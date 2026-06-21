<?php
/**
 * Search form template.
 *
 * @package Dispatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dispatch_id = 'search-' . ( function_exists( 'wp_unique_id' ) ? wp_unique_id() : uniqid() );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $dispatch_id ); ?>"><?php esc_html_e( 'Search for:', 'dispatch' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $dispatch_id ); ?>" class="search-field"
		placeholder="<?php esc_attr_e( 'Search the newsroom…', 'dispatch' ); ?>"
		value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="search-submit button">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'dispatch' ); ?></span>
		<svg class="search-submit__icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2" />
			<line x1="15.5" y1="15.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
		</svg>
	</button>
</form>
