<?php
/**
 * Recommended companion plugins for Emporium.
 *
 * Emporium is a storefront, so it leads with the commerce plugins — Till (the
 * shop engine) and Keepsake (the wishlist) — then the shared niceties. Shows
 * admins a one-click installer so the theme arrives "packaged" with everything
 * it needs, installed straight from the WPAI Themes site. Admin only.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'emporium_companions' ) ) {
	/**
	 * The recommended plugins, installed from the WPAI Themes site.
	 *
	 * @return array<string,array<string,string>>
	 */
	function emporium_companions() {
		$base = 'https://lordbasilaiassistant-sudo.github.io/wpai-themes/downloads/';

		return array(
			'till'               => array(
				'name' => 'Till — Commerce',
				'file' => 'till/till.php',
				'desc' => 'The store engine — products, a slide-in cart, and checkout. Turns Emporium into a real shop in one click.',
				'zip'  => $base . 'till.zip',
			),
			'keepsake'           => array(
				'name' => 'Keepsake — Wishlist',
				'file' => 'keepsake/keepsake.php',
				'desc' => 'A heart on every product and a saved-items page, with a live count in the header.',
				'zip'  => $base . 'keepsake.zip',
			),
			'lumen-lightbox'     => array(
				'name' => 'Lumen',
				'file' => 'lumen-lightbox/lumen-lightbox.php',
				'desc' => 'A tasteful lightbox for product imagery and galleries.',
				'zip'  => $base . 'lumen-lightbox.zip',
			),
			'smooth-back-to-top' => array(
				'name' => 'Smooth Back to Top',
				'file' => 'smooth-back-to-top/smooth-back-to-top.php',
				'desc' => 'A floating scroll-progress "back to top" button for long shop pages.',
				'zip'  => $base . 'smooth-back-to-top.zip',
			),
			'beacon-ai-seo'      => array(
				'name' => 'Beacon — AI & SEO',
				'file' => 'beacon-ai-seo/beacon-ai-seo.php',
				'desc' => 'Automated SEO, social cards, and an llms.txt so AI agents can read your store.',
				'zip'  => $base . 'beacon-ai-seo.zip',
			),
		);
	}
}

if ( ! function_exists( 'emporium_companions_missing' ) ) {
	/**
	 * Companions that are not yet active.
	 *
	 * @return array<string,array<string,string>>
	 */
	function emporium_companions_missing() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$missing = array();
		foreach ( emporium_companions() as $slug => $plugin ) {
			if ( ! is_plugin_active( $plugin['file'] ) ) {
				$missing[ $slug ] = $plugin;
			}
		}

		return $missing;
	}
}

if ( ! function_exists( 'emporium_companions_notice' ) ) {
	/**
	 * Dismissible admin notice recommending the companion plugins.
	 *
	 * @return void
	 */
	function emporium_companions_notice() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'emporium_companions_dismissed', true ) ) {
			return;
		}

		$missing = emporium_companions_missing();
		if ( empty( $missing ) ) {
			return;
		}

		$theme   = wp_get_theme()->get( 'Name' );
		$install = wp_nonce_url( admin_url( 'admin-post.php?action=emporium_install_companions' ), 'emporium_companions' );
		$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=emporium_dismiss_companions' ), 'emporium_companions' );

		echo '<div class="notice notice-info">';
		echo '<p><strong>' . esc_html( $theme ) . '</strong> ' . esc_html__( 'is a storefront theme — add its free companion plugins to open the shop:', 'emporium' ) . '</p>';
		echo '<ul style="list-style:disc;margin-left:1.4em">';
		foreach ( $missing as $plugin ) {
			echo '<li><strong>' . esc_html( $plugin['name'] ) . '</strong> &mdash; ' . esc_html( $plugin['desc'] ) . '</li>';
		}
		echo '</ul>';
		echo '<p><a class="button button-primary" href="' . esc_url( $install ) . '">' . esc_html__( 'Install & activate all', 'emporium' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $dismiss ) . '">' . esc_html__( 'Dismiss', 'emporium' ) . '</a></p>';
		echo '</div>';
	}
	add_action( 'admin_notices', 'emporium_companions_notice' );
}

if ( ! function_exists( 'emporium_companions_install' ) ) {
	/**
	 * One-click install + activate of every missing companion, from our zips.
	 * Till is installed first so Keepsake can hook into it on activation.
	 *
	 * @return void
	 */
	function emporium_companions_install() {
		if ( ! current_user_can( 'install_plugins' ) || ! check_admin_referer( 'emporium_companions' ) ) {
			wp_die( esc_html__( 'You are not allowed to install plugins.', 'emporium' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$installed = get_plugins();

		foreach ( emporium_companions() as $plugin ) {
			if ( is_plugin_active( $plugin['file'] ) ) {
				continue;
			}

			if ( ! isset( $installed[ $plugin['file'] ] ) ) {
				$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
				$upgrader->install( $plugin['zip'] );
			}

			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['file'] ) ) {
				activate_plugin( $plugin['file'] );
			}
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) );
		exit;
	}
	add_action( 'admin_post_emporium_install_companions', 'emporium_companions_install' );
}

if ( ! function_exists( 'emporium_companions_dismiss' ) ) {
	/**
	 * Remember that this admin dismissed the recommendation.
	 *
	 * @return void
	 */
	function emporium_companions_dismiss() {
		if ( ! current_user_can( 'install_plugins' ) || ! check_admin_referer( 'emporium_companions' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'emporium' ) );
		}
		update_user_meta( get_current_user_id(), 'emporium_companions_dismissed', 1 );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
	add_action( 'admin_post_emporium_dismiss_companions', 'emporium_companions_dismiss' );
}
