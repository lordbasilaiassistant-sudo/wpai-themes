<?php
/**
 * Footer template.
 *
 * @package Almanac
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .site-wrap -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap site-footer__inner">
		<p class="site-footer__brand">
			<?php almanac_leaf_mark( 'site-footer__leaf' ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
		</p>

		<?php if ( has_nav_menu( 'social' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'almanac' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'social',
					'menu_class'     => 'footer-nav__list',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				) );
				?>
			</nav>
		<?php endif; ?>

		<p class="site-footer__credit">
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s', 'almanac' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
			<span class="site-footer__sep" aria-hidden="true">&middot;</span>
			<?php esc_html_e( 'Tended with Almanac', 'almanac' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
