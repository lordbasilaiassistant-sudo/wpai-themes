<?php
/**
 * Header template.
 *
 * @package Dispatch
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
	<script>
		/* Flag that JS is on so the no-JS fallback shows all content. motion.js
		   adds the `dispatch-motion` class (which arms the reveal start-states
		   and the ticker marquee) only after confirming support and motion
		   preferences — so if scripts fail, nothing is ever left hidden. */
		document.documentElement.className += ' js';
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'dispatch' ); ?></a>

<div class="breaking-bar">
	<div class="site-wrap breaking-bar__inner">
		<span class="breaking-bar__label">
			<span class="breaking-bar__dot" aria-hidden="true"></span>
			<?php esc_html_e( 'Breaking', 'dispatch' ); ?>
		</span>
		<span class="breaking-bar__date">
			<?php echo esc_html( date_i18n( 'l, F j, Y' ) ); ?>
		</span>
	</div>
</div>

<header class="site-header">
	<div class="site-wrap site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php endif; ?>
			<div class="site-branding__text">
				<?php
				$dispatch_title_tag = ( is_front_page() && is_home() ) ? 'h1' : 'p';
				printf(
					'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
					esc_attr( $dispatch_title_tag ),
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
				<?php
				$dispatch_desc = get_bloginfo( 'description', 'display' );
				if ( $dispatch_desc || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $dispatch_desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'dispatch' ); ?>">
				<button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
					<span class="nav-toggle__bar" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'dispatch' ); ?></span>
				</button>
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
	</div>
</header>

<?php
// The signature live headline ticker, shown on the blog home only.
if ( ( is_home() || is_front_page() ) && function_exists( 'dispatch_ticker' ) ) {
	dispatch_ticker();
}
?>

<main id="content" class="site-main">
	<div class="site-wrap">
