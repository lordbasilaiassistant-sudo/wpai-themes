<?php
/**
 * Single post / page content partial.
 *
 * Renders the long-form reading view: category eyebrow, large title, byline,
 * a full-bleed cover image, and the body (with its drop-cap lead-in styled in
 * CSS). The same partial is used for pages, minus the post meta.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sonnet_is_post = ( 'post' === get_post_type() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?> data-sonnet-stagger>

	<header class="entry-header entry-header--single" data-sonnet-reveal>
		<?php if ( $sonnet_is_post ) : ?>
			<?php
			$sonnet_cat = sonnet_primary_category();
			if ( $sonnet_cat ) :
				?>
				<p class="entry-kicker"><span class="entry-kicker__cat"><?php echo esc_html( $sonnet_cat ); ?></span></p>
			<?php endif; ?>
		<?php endif; ?>

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<?php if ( $sonnet_is_post ) : ?>
			<p class="entry-meta entry-meta--single"><?php sonnet_posted_meta(); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="featured-image featured-image--single" data-sonnet-reveal>
			<?php
			the_post_thumbnail( 'large', array( 'fetchpriority' => 'high' ) );
			$sonnet_caption = wp_get_attachment_caption( get_post_thumbnail_id() );
			if ( $sonnet_caption ) :
				?>
				<figcaption class="featured-image__caption"><?php echo wp_kses_post( $sonnet_caption ); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<?php
	/**
	 * Fires after the entry header (title, meta, featured image) and immediately
	 * before the_content() — outside the .entry-content wrapper so hooked output
	 * (e.g. the WPAI Reading Time badge and Contents/TOC box) can use the full
	 * article width. Part of the WPAI companions integration contract.
	 */
	do_action( 'wpai_entry_top' );
	?>

	<div class="entry-content" data-sonnet-reveal>
		<?php
		the_content();

		wp_link_pages( array(
			'before'      => '<nav class="page-links" aria-label="' . esc_attr__( 'Post pages', 'sonnet' ) . '">' . esc_html__( 'Pages:', 'sonnet' ) . ' ',
			'after'       => '</nav>',
			'link_before' => '<span>',
			'link_after'  => '</span>',
		) );
		?>
	</div>

	<?php
	/**
	 * Fires immediately after the_content()/wp_link_pages() and before the entry
	 * footer (tags/categories) and comments — outside the .entry-content wrapper
	 * so hooked output (e.g. the WPAI Kindred related-posts block) can use the
	 * full article width. Part of the WPAI companions integration contract.
	 */
	do_action( 'wpai_entry_bottom' );
	?>

	<?php if ( $sonnet_is_post ) : ?>
		<footer class="entry-footer">
			<?php
			$sonnet_tags = get_the_tag_list( '<span class="entry-tag">', '</span><span class="entry-tag">', '</span>' );
			if ( $sonnet_tags ) {
				echo '<div class="entry-tags">' . wp_kses_post( $sonnet_tags ) . '</div>';
			}

			$sonnet_cats = get_the_category_list( '<span class="sep">·</span>' );
			if ( $sonnet_cats ) {
				echo '<p class="entry-cats"><span class="entry-cats__label">' . esc_html__( 'Filed under', 'sonnet' ) . '</span> ' . wp_kses_post( $sonnet_cats ) . '</p>';
			}
			?>
		</footer>
	<?php endif; ?>
</article>
