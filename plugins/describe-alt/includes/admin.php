<?php
/**
 * Admin status panel for Describe — Auto Alt Text.
 *
 * Describe is fully zero-config: it works the instant it's activated and has no
 * settings to set. This optional read-only panel (Media > Auto Alt Text) simply
 * shows the value the plugin is delivering — what share of the image library has
 * alt text — plus a one-click "backfill the existing library" action so older
 * uploads get real, stored alt too (not just the runtime front-end fill).
 *
 * The coverage figure is an aggregate count over all image attachments, so it is
 * cached in a transient and rebuilt only when invalidated (on upload, on the
 * backfill action, on deactivation). Bails early when not applicable.
 *
 * @package DescribeAlt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Transient lifetime for the cached coverage stat (12 hours).
 */
const DESCRIBE_ALT_COVERAGE_TTL = 12 * HOUR_IN_SECONDS;

/**
 * Compute image-library alt-text coverage, cached in a transient.
 *
 * Returns an array: total images, images with alt, images without, and the
 * percentage covered. The two COUNT queries run only on a cache miss; the
 * result is invalidated whenever coverage can change (upload, backfill).
 *
 * @return array{total:int,with_alt:int,without_alt:int,percent:int}
 */
function describe_alt_get_coverage() {
	$cached = get_transient( 'describe_alt_coverage' );
	if ( is_array( $cached ) && isset( $cached['total'], $cached['with_alt'] ) ) {
		return $cached;
	}

	global $wpdb;

	// Total image attachments.
	$total = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		 WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
	);

	// Image attachments that have a non-empty alt meta.
	$with_alt = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} m
		     ON m.post_id = p.ID AND m.meta_key = '_wp_attachment_image_alt'
		 WHERE p.post_type = 'attachment'
		   AND p.post_mime_type LIKE 'image/%'
		   AND TRIM(m.meta_value) <> ''"
	);

	$without_alt = max( 0, $total - $with_alt );
	$percent     = $total > 0 ? (int) round( $with_alt / $total * 100 ) : 100;

	$coverage = array(
		'total'       => $total,
		'with_alt'    => $with_alt,
		'without_alt' => $without_alt,
		'percent'     => $percent,
	);

	set_transient( 'describe_alt_coverage', $coverage, DESCRIBE_ALT_COVERAGE_TTL );

	return $coverage;
}

/**
 * Drop the cached coverage stat so the next view recomputes it.
 *
 * The figure is an aggregate over the whole image library, so it goes stale the
 * moment an image is added, gains/loses alt, or is removed. The upload, edit and
 * backfill paths invalidate it themselves; this hook closes the deletion case so
 * the gauge can never report a phantom image after an attachment is trashed or
 * permanently deleted. Cheap (a single transient delete), and the recompute is
 * lazy — it only happens the next time the panel is actually viewed.
 *
 * @param int $post_id The post being deleted.
 * @return void
 */
function describe_alt_invalidate_coverage( $post_id ) {
	// `delete_attachment` only fires for attachments, so this is already scoped;
	// the type check keeps the same function safe to reuse from generic hooks.
	if ( 'attachment' !== get_post_type( $post_id ) ) {
		return;
	}

	delete_transient( 'describe_alt_coverage' );
}
add_action( 'delete_attachment', 'describe_alt_invalidate_coverage' );

// Also refresh coverage whenever an attachment is saved: a human may have typed
// or cleared alt directly in the Media Library, which the fill path deliberately
// leaves alone — so its own invalidation never fires. Hooking the save here keeps
// the (lazily recomputed) gauge honest without coupling it to the fill logic.
add_action( 'edit_attachment', 'describe_alt_invalidate_coverage' );

/**
 * Register the status page under the Media menu.
 *
 * Captures the hook suffix so assets load only on this screen.
 *
 * @return void
 */
function describe_alt_add_admin_page() {
	$hook = add_media_page(
		__( 'Auto Alt Text', 'describe-alt' ),
		__( 'Auto Alt Text', 'describe-alt' ),
		'manage_options',
		'describe-alt',
		'describe_alt_render_admin_page'
	);

	if ( $hook ) {
		add_action( 'load-' . $hook, 'describe_alt_admin_load' );
	}
}
add_action( 'admin_menu', 'describe_alt_add_admin_page' );

/**
 * On-load handler for the status screen: process the backfill action, then
 * register the page-scoped asset enqueue.
 *
 * @return void
 */
function describe_alt_admin_load() {
	describe_alt_maybe_run_backfill();
	add_action( 'admin_enqueue_scripts', 'describe_alt_admin_enqueue' );
}

/**
 * Enqueue the status-page assets (real, versioned files, this screen only).
 *
 * The stylesheet draws the gauge; the script is pure progressive enhancement
 * (a motion-guarded count-up) and is deferred so it never blocks the page. Both
 * load exclusively on Media > Auto Alt Text.
 *
 * @return void
 */
function describe_alt_admin_enqueue() {
	wp_enqueue_style(
		'describe-alt-admin',
		plugins_url( 'assets/css/admin.css', DESCRIBE_ALT_FILE ),
		array(),
		DESCRIBE_ALT_VERSION
	);

	wp_enqueue_script(
		'describe-alt-admin',
		plugins_url( 'assets/js/admin.js', DESCRIBE_ALT_FILE ),
		array(),
		DESCRIBE_ALT_VERSION,
		true // Load in the footer.
	);
}

/**
 * Number of attachments to process per backfill click.
 *
 * Kept modest so the action always completes well within a request, even on a
 * large library; the user can click again to continue. Filterable.
 *
 * @return int
 */
function describe_alt_backfill_batch_size() {
	$size = (int) apply_filters( 'describe_alt_backfill_batch_size', 200 );

	return $size > 0 ? $size : 200;
}

/**
 * Handle the "backfill existing library" form submission.
 *
 * Nonce-verified and capability-gated. Walks a batch of image attachments that
 * have no stored alt, derives one for each, and writes it — never overwriting a
 * human's alt (the query only selects images whose alt is absent or empty). The
 * outcome is stashed in a transient and surfaced as an admin notice after the
 * post-redirect-get, so a refresh won't re-run it.
 *
 * @return void
 */
function describe_alt_maybe_run_backfill() {
	if ( empty( $_POST['describe_alt_backfill'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'describe-alt' ) );
	}

	check_admin_referer( 'describe_alt_backfill', 'describe_alt_nonce' );

	$ids       = describe_alt_find_missing_alt_ids( describe_alt_backfill_batch_size() );
	$filled    = 0;
	$skipped   = 0;

	foreach ( $ids as $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		// Defensive re-check: never touch an image that gained alt meanwhile.
		if ( '' !== describe_alt_get_stored( $attachment_id ) ) {
			continue;
		}

		$alt = describe_alt_derive( $attachment_id );

		if ( '' === $alt ) {
			$skipped++;
			continue;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
		$filled++;
	}

	// Coverage changed — drop the cache so the next view recomputes.
	delete_transient( 'describe_alt_coverage' );

	set_transient(
		'describe_alt_backfill_result',
		array(
			'filled'  => $filled,
			'skipped' => $skipped,
		),
		MINUTE_IN_SECONDS
	);

	wp_safe_redirect( add_query_arg( array( 'page' => 'describe-alt', 'backfilled' => 1 ), admin_url( 'upload.php' ) ) );
	exit;
}

/**
 * Find IDs of image attachments that currently have no stored alt text.
 *
 * A LEFT JOIN on the alt meta lets us catch both "no meta row" and
 * "empty/whitespace meta" in one pass. Returns at most $limit IDs, newest first
 * so the most recently-uploaded gaps are closed first.
 *
 * @param int $limit Maximum IDs to return.
 * @return int[] Attachment IDs lacking alt.
 */
function describe_alt_find_missing_alt_ids( $limit ) {
	global $wpdb;

	$limit = max( 1, (int) $limit );

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} m
			     ON m.post_id = p.ID AND m.meta_key = '_wp_attachment_image_alt'
			 WHERE p.post_type = 'attachment'
			   AND p.post_mime_type LIKE 'image/%'
			   AND ( m.meta_id IS NULL OR TRIM(m.meta_value) = '' )
			 ORDER BY p.ID DESC
			 LIMIT %d",
			$limit
		)
	);

	return array_map( 'intval', (array) $ids );
}

/**
 * Render the status / backfill page.
 *
 * @return void
 */
function describe_alt_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$coverage = describe_alt_get_coverage();
	$result   = get_transient( 'describe_alt_backfill_result' );
	if ( $result ) {
		delete_transient( 'describe_alt_backfill_result' );
	}

	$percent     = (int) $coverage['percent'];
	$total       = (int) $coverage['total'];
	$with_alt    = (int) $coverage['with_alt'];
	$without_alt = (int) $coverage['without_alt'];
	$gauge_id    = 'describe-alt-gauge';
	?>
	<div class="wrap describe-alt-wrap">
		<h1><?php echo esc_html__( 'Auto Alt Text', 'describe-alt' ); ?></h1>

		<?php if ( is_array( $result ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: number of images filled, 2: number skipped. */
						esc_html__( 'Backfill complete: alt text written for %1$d image(s); %2$d had nothing meaningful to derive and were left empty.', 'describe-alt' ),
						(int) $result['filled'],
						(int) $result['skipped']
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<p class="describe-alt-lede">
			<?php echo esc_html__( 'Describe runs itself — no settings. Every new image gets meaningful alt text on upload, and anything still missing alt is filled automatically when your pages render. This panel just shows how your library is doing.', 'describe-alt' ); ?>
		</p>

		<div class="describe-alt-grid">
			<section class="describe-alt-card describe-alt-card--gauge" aria-labelledby="<?php echo esc_attr( $gauge_id ); ?>">
				<h2 id="<?php echo esc_attr( $gauge_id ); ?>" class="describe-alt-card__title">
					<?php echo esc_html__( 'Alt-text coverage', 'describe-alt' ); ?>
				</h2>

				<div class="describe-alt-meter" role="img"
					aria-label="<?php
						/* translators: %d: coverage percentage. */
						echo esc_attr( sprintf( __( '%d%% of images have alt text', 'describe-alt' ), $percent ) );
					?>"
					style="--describe-alt-percent:<?php echo esc_attr( (string) $percent ); ?>">
					<span class="describe-alt-meter__value"><?php echo esc_html( (string) $percent ); ?><span class="describe-alt-meter__unit">%</span></span>
				</div>

				<dl class="describe-alt-stats">
					<div class="describe-alt-stat">
						<dt><?php echo esc_html__( 'Images total', 'describe-alt' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $total ) ); ?></dd>
					</div>
					<div class="describe-alt-stat">
						<dt><?php echo esc_html__( 'With alt text', 'describe-alt' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $with_alt ) ); ?></dd>
					</div>
					<div class="describe-alt-stat">
						<dt><?php echo esc_html__( 'Still empty', 'describe-alt' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $without_alt ) ); ?></dd>
					</div>
				</dl>
			</section>

			<section class="describe-alt-card" aria-labelledby="describe-alt-backfill-heading">
				<h2 id="describe-alt-backfill-heading" class="describe-alt-card__title">
					<?php echo esc_html__( 'Backfill your existing library', 'describe-alt' ); ?>
				</h2>
				<p class="describe-alt-card__desc">
					<?php echo esc_html__( 'New uploads are handled for you automatically. To write permanent, editable alt text into older images that are still empty, run a backfill. It derives alt from each image\'s title, caption, or filename — and never touches alt you wrote yourself.', 'describe-alt' ); ?>
				</p>

				<?php if ( $without_alt > 0 ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'upload.php?page=describe-alt' ) ); ?>">
						<?php wp_nonce_field( 'describe_alt_backfill', 'describe_alt_nonce' ); ?>
						<input type="hidden" name="describe_alt_backfill" value="1" />
						<p>
							<button type="submit" class="button button-primary">
								<?php
								printf(
									/* translators: %d: batch size. */
									esc_html__( 'Backfill up to %d images', 'describe-alt' ),
									(int) describe_alt_backfill_batch_size()
								);
								?>
							</button>
						</p>
						<p class="describe-alt-hint">
							<?php echo esc_html__( 'Large library? Run it more than once — each pass closes the next batch of gaps.', 'describe-alt' ); ?>
						</p>
					</form>
				<?php else : ?>
					<p class="describe-alt-done">
						<?php echo esc_html__( 'Every image in your library has alt text. Nothing to backfill.', 'describe-alt' ); ?>
					</p>
				<?php endif; ?>
			</section>
		</div>

		<p class="describe-alt-foot">
			<?php echo esc_html__( 'Self-contained: no external services, no AI APIs, no network calls. Everything is derived locally from your own metadata.', 'describe-alt' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Add a "Auto Alt Text" action link on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function describe_alt_plugin_action_links( $links ) {
	$link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'upload.php?page=describe-alt' ) ),
		esc_html__( 'Auto Alt Text', 'describe-alt' )
	);

	array_unshift( $links, $link );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( DESCRIBE_ALT_FILE ), 'describe_alt_plugin_action_links' );
