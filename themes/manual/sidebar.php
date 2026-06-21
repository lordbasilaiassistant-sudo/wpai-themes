<?php
/**
 * Left rail template — the documentation navigation tree.
 *
 * The signature of Manual: a sticky, left-hand docs/sections tree. If the site
 * owner has activated the classic widget area, those widgets render instead;
 * otherwise the curated docs menu (or a built-from-content fallback) appears.
 *
 * @package Manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="layout__aside" aria-label="<?php esc_attr_e( 'Documentation navigation', 'manual' ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<div class="widget-area">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	<?php else : ?>
		<?php manual_docs_nav(); ?>
	<?php endif; ?>
</aside>
