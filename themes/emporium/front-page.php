<?php
/**
 * Front page — the storefront.
 *
 * A confident hero, a featured-product grid, shoppable category tiles, a promo
 * band, social proof, and a journal teaser. Every store section is guarded by
 * emporium_has_store(), so with the Till plugin inactive the page still reads
 * as a polished landing page driven by your latest posts.
 *
 * @package Emporium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$emporium_store = emporium_has_store();
$emporium_cats  = $emporium_store ? emporium_shop_categories( 4 ) : array();
?>

<!-- Hero -->
<section class="em-section em-section--tight">
	<div class="site-wrap em-hero">
		<div class="em-hero__copy">
			<p class="em-label"><?php esc_html_e( 'New season', 'emporium' ); ?></p>
			<h2 class="em-hero__title"><?php echo esc_html( get_theme_mod( 'emporium_hero_title', __( 'Things worth keeping.', 'emporium' ) ) ); ?></h2>
			<p class="em-hero__lead"><?php echo esc_html( get_theme_mod( 'emporium_hero_lead', __( 'A small, considered collection for the home — made well, priced fairly, and shipped free.', 'emporium' ) ) ); ?></p>
			<div class="em-hero__cta">
				<?php if ( $emporium_store ) : ?>
					<a class="em-btn" href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php esc_html_e( 'Shop the collection', 'emporium' ); ?></a>
					<?php
					$emporium_first_cat = ! empty( $emporium_cats ) ? $emporium_cats[0] : null;
					if ( $emporium_first_cat ) :
						?>
						<a class="em-btn em-btn--ghost" href="<?php echo esc_url( $emporium_first_cat['url'] ); ?>"><?php echo esc_html( sprintf( __( 'Browse %s', 'emporium' ), $emporium_first_cat['name'] ) ); ?></a>
					<?php endif; ?>
				<?php else : ?>
					<a class="em-btn" href="#journal"><?php esc_html_e( 'Read the journal', 'emporium' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="em-hero__art">
			<div class="em-hero__art-ph" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M3 9l1.5 10.5A1.5 1.5 0 006 21h12a1.5 1.5 0 001.5-1.3L21 9M3 9h18M3 9l3-5h12l3 5M8.5 13a3.5 3.5 0 007 0"/></svg>
			</div>
			<div class="em-hero__tag">
				<?php esc_html_e( 'Best seller', 'emporium' ); ?>
				<strong><?php esc_html_e( 'Boucle Accent Chair', 'emporium' ); ?></strong>
			</div>
		</div>
	</div>
</section>

<!-- Trust signals -->
<div class="site-wrap">
	<div class="em-trust">
		<span class="em-trust__item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7zM7 19a2 2 0 100-4 2 2 0 000 4zM18 19a2 2 0 100-4 2 2 0 000 4z"/></svg><?php esc_html_e( 'Free shipping over $50', 'emporium' ); ?></span>
		<span class="em-trust__item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0zM12 7v5l3 2"/></svg><?php esc_html_e( '30-day easy returns', 'emporium' ); ?></span>
		<span class="em-trust__item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3l7 4v5c0 4-3 7-7 9-4-2-7-5-7-9V7z"/><path d="M9 12l2 2 4-4"/></svg><?php esc_html_e( 'Secure checkout', 'emporium' ); ?></span>
		<span class="em-trust__item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 17.3l-6.2 3.7 1.6-7L2 9.2l7.1-.6L12 2l2.9 6.6 7.1.6-5.4 4.8 1.6 7z"/></svg><?php esc_html_e( '4.8/5 from 2,400 reviews', 'emporium' ); ?></span>
	</div>
</div>

<?php if ( $emporium_store ) : ?>

	<!-- Featured products -->
	<section class="em-section">
		<div class="site-wrap">
			<div class="section-head">
				<div>
					<p class="em-label"><?php esc_html_e( 'Just in', 'emporium' ); ?></p>
					<h2 class="section-head__title"><?php esc_html_e( 'New arrivals', 'emporium' ); ?></h2>
				</div>
				<a class="section-head__link" href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php esc_html_e( 'Shop all', 'emporium' ); ?></a>
			</div>
			<?php
			echo emporium_featured_products( 8 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- product cards escaped in Till.
			?>
		</div>
	</section>

	<!-- Category tiles -->
	<?php if ( ! empty( $emporium_cats ) ) : ?>
		<section class="em-section em-section--tight">
			<div class="site-wrap">
				<div class="section-head">
					<div>
						<p class="em-label"><?php esc_html_e( 'Find your thing', 'emporium' ); ?></p>
						<h2 class="section-head__title"><?php esc_html_e( 'Shop by category', 'emporium' ); ?></h2>
					</div>
				</div>
				<div class="em-cats">
					<?php foreach ( $emporium_cats as $emporium_i => $emporium_cat ) : ?>
						<a class="em-cat" href="<?php echo esc_url( $emporium_cat['url'] ); ?>">
							<?php if ( $emporium_cat['thumb'] ) : ?>
								<img class="em-cat__bg" src="<?php echo esc_url( $emporium_cat['thumb'] ); ?>" alt="" loading="lazy" decoding="async" />
							<?php else : ?>
								<span class="em-cat__bg" style="background:linear-gradient(135deg,hsl(<?php echo (int) ( $emporium_i * 70 ); ?> 40% 70%),hsl(<?php echo (int) ( $emporium_i * 70 + 40 ); ?> 38% 52%));" aria-hidden="true"></span>
							<?php endif; ?>
							<span>
								<span class="em-cat__name"><?php echo esc_html( $emporium_cat['name'] ); ?></span>
								<span class="em-cat__count"><?php echo esc_html( sprintf( _n( '%d item', '%d items', $emporium_cat['count'], 'emporium' ), $emporium_cat['count'] ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Promo band -->
	<section class="em-section em-section--tight">
		<div class="site-wrap">
			<div class="em-promo">
				<div>
					<p class="em-label" style="color:#fff"><?php esc_html_e( 'Member offer', 'emporium' ); ?></p>
					<h2><?php esc_html_e( 'Save 15% on your first order', 'emporium' ); ?></h2>
					<p><?php esc_html_e( 'Join our list for early access to new drops, restock alerts, and a welcome code for your first purchase.', 'emporium' ); ?></p>
				</div>
				<a class="em-btn" href="<?php echo esc_url( emporium_shop_url() ); ?>"><?php esc_html_e( 'Start shopping', 'emporium' ); ?></a>
			</div>
		</div>
	</section>

<?php endif; ?>

<!-- Journal -->
<?php
$emporium_blog = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
if ( $emporium_blog->have_posts() ) :
	?>
	<section class="em-section" id="journal">
		<div class="site-wrap">
			<div class="section-head">
				<div>
					<p class="em-label"><?php esc_html_e( 'Stories', 'emporium' ); ?></p>
					<h2 class="section-head__title"><?php esc_html_e( 'From the journal', 'emporium' ); ?></h2>
				</div>
				<a class="section-head__link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'All stories', 'emporium' ); ?></a>
			</div>
			<div class="em-journal">
				<?php
				while ( $emporium_blog->have_posts() ) :
					$emporium_blog->the_post();
					emporium_post_card();
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- Newsletter -->
<section class="em-section em-section--tight">
	<div class="site-wrap">
		<div class="em-news">
			<p class="em-label"><?php esc_html_e( 'Stay in touch', 'emporium' ); ?></p>
			<h2><?php esc_html_e( 'Good things, now and then', 'emporium' ); ?></h2>
			<p><?php esc_html_e( 'A short, occasional note when something new lands. No spam, unsubscribe anytime.', 'emporium' ); ?></p>
			<form class="em-news__form" onsubmit="return false;">
				<label class="screen-reader-text" for="em-news-email"><?php esc_html_e( 'Email address', 'emporium' ); ?></label>
				<input id="em-news-email" type="email" placeholder="<?php esc_attr_e( 'you@example.com', 'emporium' ); ?>" />
				<button class="em-btn" type="submit"><?php esc_html_e( 'Subscribe', 'emporium' ); ?></button>
			</form>
		</div>
	</div>
</section>

<?php
get_footer();
