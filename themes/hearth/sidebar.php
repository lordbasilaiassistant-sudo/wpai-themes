<?php
/**
 * Sidebar template.
 *
 * @package Hearth
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="layout__aside widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'hearth' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
