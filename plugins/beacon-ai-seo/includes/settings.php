<?php
/**
 * Settings for Beacon — AI & SEO.
 *
 * Beacon is zero-config: it produces correct output the moment it is activated.
 * This optional, single-section settings page adds real value for two things
 * the plugin cannot infer reliably:
 *
 *   1. Whether the site represents an Organization or a Person (changes the
 *      publisher node type in JSON-LD).
 *   2. Social profile URLs, which become schema.org `sameAs` links and let
 *      Beacon derive the Twitter card handle.
 *
 * Everything is sanitized on input, escaped on output, and saved through the
 * Settings API (nonces handled by WordPress).
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Option name storing the plugin settings array.
 */
const BEACON_OPTION = 'beacon_settings';

/**
 * Default settings.
 *
 * @return array
 */
function beacon_default_settings() {
	return array(
		'entity_type'     => 'organization', // 'organization' | 'person'.
		'social_profiles' => '',             // Newline-separated URLs.
	);
}

/**
 * Retrieve the merged settings array.
 *
 * @return array
 */
function beacon_get_settings() {
	$saved = get_option( BEACON_OPTION, array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), beacon_default_settings() );
}

/**
 * The configured publisher entity type ('organization' or 'person').
 *
 * @return string
 */
function beacon_get_entity_type() {
	$settings = beacon_get_settings();
	$type     = 'person' === $settings['entity_type'] ? 'person' : 'organization';

	/**
	 * Filter the publisher entity type used in JSON-LD.
	 *
	 * @param string $type 'organization' or 'person'.
	 */
	return (string) apply_filters( 'beacon_entity_type', $type );
}

/**
 * The configured social profile URLs, as a clean array.
 *
 * @return string[] Absolute URLs.
 */
function beacon_get_social_profiles() {
	$settings = beacon_get_settings();
	$lines    = preg_split( '/[\r\n]+/', (string) $settings['social_profiles'], -1, PREG_SPLIT_NO_EMPTY );

	$urls = array();
	foreach ( (array) $lines as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( '' !== $url ) {
			$urls[] = $url;
		}
	}

	/**
	 * Filter the list of social profile URLs (schema.org sameAs).
	 *
	 * @param string[] $urls Absolute profile URLs.
	 */
	return (array) apply_filters( 'beacon_social_profiles', $urls );
}

/**
 * Register the settings, section, and fields with the Settings API.
 *
 * @return void
 */
function beacon_register_settings() {
	register_setting(
		'beacon_settings_group',
		BEACON_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'beacon_sanitize_settings',
			'default'           => beacon_default_settings(),
		)
	);

	add_settings_section(
		'beacon_main_section',
		__( 'Identity & social profiles', 'beacon-ai-seo' ),
		'beacon_settings_section_intro',
		'beacon-ai-seo'
	);

	add_settings_field(
		'beacon_entity_type',
		__( 'This site represents', 'beacon-ai-seo' ),
		'beacon_field_entity_type',
		'beacon-ai-seo',
		'beacon_main_section'
	);

	add_settings_field(
		'beacon_social_profiles',
		__( 'Social profile URLs', 'beacon-ai-seo' ),
		'beacon_field_social_profiles',
		'beacon-ai-seo',
		'beacon_main_section'
	);
}
add_action( 'admin_init', 'beacon_register_settings' );

/**
 * Sanitize the settings array on save.
 *
 * @param mixed $input Raw submitted value.
 * @return array
 */
function beacon_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = beacon_default_settings();

	$entity = isset( $input['entity_type'] ) ? sanitize_key( $input['entity_type'] ) : $defaults['entity_type'];
	if ( ! in_array( $entity, array( 'organization', 'person' ), true ) ) {
		$entity = $defaults['entity_type'];
	}

	// Sanitize each profile URL line; drop anything that is not a valid URL.
	$raw_profiles = isset( $input['social_profiles'] ) ? (string) $input['social_profiles'] : '';
	$lines        = preg_split( '/[\r\n]+/', $raw_profiles, -1, PREG_SPLIT_NO_EMPTY );
	$clean        = array();

	foreach ( (array) $lines as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( '' !== $url ) {
			$clean[] = $url;
		}
	}

	return array(
		'entity_type'     => $entity,
		'social_profiles' => implode( "\n", $clean ),
	);
}

/**
 * Add the settings page under the Settings menu.
 *
 * Captures the hook suffix so assets load only on Beacon's own screen.
 *
 * @return void
 */
function beacon_add_settings_page() {
	$hook = add_options_page(
		__( 'Beacon — AI & SEO', 'beacon-ai-seo' ),
		__( 'Beacon AI & SEO', 'beacon-ai-seo' ),
		'manage_options',
		'beacon-ai-seo',
		'beacon_render_settings_page'
	);

	add_action( 'load-' . $hook, 'beacon_settings_enqueue_hook' );
}
add_action( 'admin_menu', 'beacon_add_settings_page' );

/**
 * Defer the asset enqueue to admin_enqueue_scripts, but only on our screen.
 *
 * Registering the enqueue from the page's `load-{hook}` action guarantees the
 * CSS/JS are added on Beacon's settings page and nowhere else in wp-admin.
 *
 * @return void
 */
function beacon_settings_enqueue_hook() {
	add_action( 'admin_enqueue_scripts', 'beacon_settings_enqueue_assets' );
}

/**
 * Enqueue the settings-page stylesheet and the copy-to-clipboard enhancement.
 *
 * Both are real, versioned files under /assets and load exclusively on this
 * page. The script is pure progressive enhancement: the URL link works without
 * it, and the JS only adds a one-click "copy" affordance, motion-guarded.
 *
 * @return void
 */
function beacon_settings_enqueue_assets() {
	wp_enqueue_style(
		'beacon-admin',
		plugins_url( 'assets/css/admin.css', BEACON_FILE ),
		array(),
		BEACON_VERSION
	);

	wp_enqueue_script(
		'beacon-admin',
		plugins_url( 'assets/js/admin.js', BEACON_FILE ),
		array(),
		BEACON_VERSION,
		true
	);

	wp_localize_script(
		'beacon-admin',
		'beaconAdmin',
		array(
			'copied' => __( 'Copied!', 'beacon-ai-seo' ),
			'copy'   => __( 'Copy', 'beacon-ai-seo' ),
		)
	);
}

/**
 * Intro copy for the settings section.
 *
 * @return void
 */
function beacon_settings_section_intro() {
	echo '<p>' . esc_html__(
		'Beacon works with zero configuration. These optional settings sharpen your structured data: the entity type controls how search engines and AI agents understand the site\'s publisher, and your social profiles become verifiable links.',
		'beacon-ai-seo'
	) . '</p>';
}

/**
 * Render the entity-type radio field.
 *
 * @return void
 */
function beacon_field_entity_type() {
	$current = beacon_get_settings()['entity_type'];
	$options = array(
		'organization' => __( 'An organization, company, or brand', 'beacon-ai-seo' ),
		'person'       => __( 'A person (personal site or blog)', 'beacon-ai-seo' ),
	);

	echo '<fieldset>';
	foreach ( $options as $value => $label ) {
		printf(
			'<label style="display:block;margin:.25em 0;"><input type="radio" name="%1$s[entity_type]" value="%2$s" %3$s /> %4$s</label>',
			esc_attr( BEACON_OPTION ),
			esc_attr( $value ),
			checked( $current, $value, false ),
			esc_html( $label )
		);
	}
	echo '</fieldset>';
}

/**
 * Render the social-profiles textarea field.
 *
 * @return void
 */
function beacon_field_social_profiles() {
	$value = beacon_get_settings()['social_profiles'];

	printf(
		'<textarea name="%1$s[social_profiles]" rows="5" cols="50" class="large-text code" placeholder="%2$s">%3$s</textarea>',
		esc_attr( BEACON_OPTION ),
		esc_attr__( 'https://twitter.com/yourhandle', 'beacon-ai-seo' ),
		esc_textarea( $value )
	);

	echo '<p class="description">' . esc_html__(
		'One full profile URL per line (Twitter/X, Facebook, LinkedIn, GitHub, …). These are output as schema.org sameAs links; a twitter.com or x.com URL also sets your Twitter card handle.',
		'beacon-ai-seo'
	) . '</p>';
}

/**
 * Render the settings page wrapper and form.
 *
 * @return void
 */
function beacon_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$llms_url = home_url( '/llms.txt' );

	?>
	<div class="wrap beacon-wrap">
		<h1><?php echo esc_html__( 'Beacon — AI & SEO', 'beacon-ai-seo' ); ?></h1>

		<div class="beacon-card" role="group" aria-labelledby="beacon-llms-heading">
			<h2 id="beacon-llms-heading" class="beacon-card__title">
				<?php echo esc_html__( 'Your AI index is live', 'beacon-ai-seo' ); ?>
			</h2>
			<p class="beacon-card__desc">
				<?php echo esc_html__( 'LLMs and AI agents can read a clean, machine-readable map of your site here:', 'beacon-ai-seo' ); ?>
			</p>
			<div class="beacon-url-row">
				<a class="beacon-url" href="<?php echo esc_url( $llms_url ); ?>" target="_blank" rel="noopener noreferrer" data-beacon-url="<?php echo esc_url( $llms_url ); ?>"><?php echo esc_html( $llms_url ); ?></a>
				<button type="button" class="button beacon-copy" data-beacon-copy="<?php echo esc_url( $llms_url ); ?>">
					<?php echo esc_html__( 'Copy', 'beacon-ai-seo' ); ?>
				</button>
			</div>
			<p class="beacon-status" role="status" aria-live="polite"></p>
		</div>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'beacon_settings_group' );
			do_settings_sections( 'beacon-ai-seo' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Add a "Settings" action link on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function beacon_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=beacon-ai-seo' ) ),
		esc_html__( 'Settings', 'beacon-ai-seo' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( BEACON_FILE ), 'beacon_plugin_action_links' );
