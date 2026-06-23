<?php
/**
 * Header template.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'emporium' ); ?></a>

<header class="site-header" id="site-header">

	<?php
	// A simple promo announcement bar, only when the store is live.
	if ( emporium_has_store() ) :
		?>
		<div class="em-announce">
			<?php
			printf(
				/* translators: %s: shop link. */
				esc_html__( 'Free shipping on every order this week — %s', 'emporium' ),
				'<a href="' . esc_url( emporium_shop_url() ) . '">' . esc_html__( 'shop new arrivals', 'emporium' ) . '</a>'
			);
			?>
		</div>
	<?php endif; ?>

	<div class="site-wrap site-header__inner">

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu" aria-label="<?php esc_attr_e( 'Menu', 'emporium' ); ?>">
				<span class="nav-toggle__bar" aria-hidden="true"></span>
			</button>
		<?php endif; ?>

		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php endif; ?>
			<div class="site-branding__text">
				<?php
				$emporium_title_tag = ( is_front_page() && is_home() ) ? 'h1' : 'p';
				printf(
					'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
					esc_attr( $emporium_title_tag ),
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				$emporium_desc = get_bloginfo( 'description', 'display' );
				if ( $emporium_desc || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $emporium_desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="main-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'emporium' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'main-nav__list',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 2,
				) );
				?>
			</nav>
		<?php endif; ?>

		<div class="site-actions">
			<button class="site-actions__search" aria-expanded="false" aria-controls="em-search" aria-label="<?php esc_attr_e( 'Search', 'emporium' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2" />
					<line x1="15.5" y1="15.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
				</svg>
			</button>
			<?php emporium_wishlist_link(); ?>
			<?php emporium_cart_button(); ?>
		</div>
	</div>

	<div class="em-search-panel" id="em-search">
		<?php get_search_form(); ?>
	</div>
</header>

<main id="content" class="site-main">
