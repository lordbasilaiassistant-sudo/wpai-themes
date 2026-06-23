<?php
/**
 * Footer template.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap">
		<div class="site-footer__top">
			<div class="footer-col footer-col--brand">
				<p class="site-footer__brand"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a></p>
				<?php
				$emporium_footer_desc = get_bloginfo( 'description', 'display' );
				if ( $emporium_footer_desc ) :
					?>
					<p class="site-footer__tagline"><?php echo esc_html( $emporium_footer_desc ); ?></p>
				<?php else : ?>
					<p class="site-footer__tagline"><?php esc_html_e( 'Considered goods, delivered with care. Free shipping, easy returns.', 'emporium' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( emporium_has_store() ) : ?>
				<div class="footer-col">
					<h2 class="footer-col__title"><?php esc_html_e( 'Shop', 'emporium' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php esc_html_e( 'All products', 'emporium' ); ?></a></li>
						<?php
						$emporium_foot_cats = emporium_shop_categories( 4 );
						foreach ( $emporium_foot_cats as $emporium_fc ) :
							?>
							<li><a href="<?php echo esc_url( $emporium_fc['url'] ); ?>"><?php echo esc_html( $emporium_fc['name'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="footer-col">
				<h2 class="footer-col__title"><?php esc_html_e( 'Company', 'emporium' ); ?></h2>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'Footer navigation', 'emporium' ); ?>">
						<?php
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-nav__list',
							'container'      => false,
							'fallback_cb'    => false,
							'depth'          => 1,
						) );
						?>
					</nav>
				<?php else : ?>
					<ul class="site-footer__menu">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'emporium' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/?page_id=2' ) ); ?>"><?php esc_html_e( 'About', 'emporium' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<h2 class="footer-col__title"><?php esc_html_e( 'Help', 'emporium' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'Shipping & returns', 'emporium' ); ?></li>
					<li><?php esc_html_e( 'Track an order', 'emporium' ); ?></li>
					<li><?php esc_html_e( 'Contact us', 'emporium' ); ?></li>
				</ul>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p>
				<?php
				printf(
					/* translators: 1: year, 2: site name */
					esc_html__( '© %1$s %2$s — built with the free Emporium theme.', 'emporium' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
			<div class="em-pay" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'emporium' ); ?>">
				<span>VISA</span><span>MC</span><span>AMEX</span><span>PAY</span>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
