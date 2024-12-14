<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets;

use Amazon_S3_And_CloudFront_Pro;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\API\V1\State;
use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\API\V1\Assets_Domain_Check;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\API\V1\Assets_Settings;
use DeliciousBrains\WP_Offload_Media\Settings\Domain_Check;
use DeliciousBrains\WP_Offload_Media\Settings\Exceptions\Domain_Check_Exception;
use DeliciousBrains\WP_Offload_Media\Settings\Validator_Interface;
use DeliciousBrains\WP_Offload_Media\Settings_Interface;
use DeliciousBrains\WP_Offload_Media\Settings_Trait;
use DeliciousBrains\WP_Offload_Media\Settings_Validator_Trait;
use Exception;
use WP_Error as AS3CF_Result;

class Assets extends Integration implements Settings_Interface, Validator_Interface {
	use Settings_Trait;
	use Settings_Validator_Trait;

	const SETTINGS_KEY  = 'as3cf_assets_pull';
	const VALIDATOR_KEY = 'assets';

	/**
	 * @var Amazon_S3_And_CloudFront_Pro
	 */
	protected $as3cf;

	/**
	 * Cache property of whether integration is enabled.
	 *
	 * @var bool
	 */
	private $enabled;

	/**
	 * @var array
	 */
	protected static $settings_constants = array(
		'AS3CF_ASSETS_PULL_SETTINGS',
		'WPOS3_ASSETS_PULL_SETTINGS',
	);

	/**
	 * @inheritDoc
	 */
	public static function is_installed(): bool {
		return true;
	}

	/**
	 * Is this integration enabled?
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		if ( is_null( $this->enabled ) ) {
			if ( parent::is_enabled() && $this->as3cf->feature_enabled( 'assets' ) ) {
				$this->enabled = true;
			} else {
				$this->enabled = false;
			}
		}

		return $this->enabled;
	}

	/**
	 * @inheritDoc
	 */
	public function init() {
		// UI - Common
		add_filter( 'as3cfpro_js_strings', array( $this, 'add_js_strings' ) );

		// Don't enable the remaining hooks unless integration enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		// UI - Enabled
		add_filter( 'as3cfpro_js_config', array( $this, 'add_js_config' ) );
		add_filter( 'as3cf_get_docs', array( $this, 'get_docs' ) );

		// REST-API
		add_filter( 'as3cf_api_endpoints', array( $this, 'add_api_endpoints' ) );
		add_filter(
			$this->as3cf->get_plugin_prefix() . '_api_response_get_' . State::name(),
			array( $this, 'api_get_state' )
		);

		// Support
		add_filter( 'as3cf_diagnostic_info', array( $this, 'diagnostic_info' ) );

		// Keep track of whether the settings we're responsible for are currently being saved.
		add_action( 'as3cf_pre_save_assets_settings', function () {
			$this->set_saving_settings( true );
		} );
		add_action( 'as3cf_post_save_assets_settings', function () {
			$this->set_saving_settings( false );
		} );

		// Register this instance as a validator.
		$this->as3cf->validation_manager->register_validator( self::VALIDATOR_KEY, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function setup() {
		// Don't enable the hooks unless integration and URL rewriting enabled.
		if ( ! $this->is_enabled() || ! $this->should_rewrite_urls() ) {
			return;
		}

		// URL Rewriting.
		add_filter( 'style_loader_src', array( $this, 'rewrite_src' ), 10, 2 );
		add_filter( 'script_loader_src', array( $this, 'rewrite_src' ), 10, 2 );
		add_filter( 'as3cf_get_asset', array( $this, 'rewrite_src' ) );
		add_filter( 'wp_resource_hints', array( $this, 'register_resource_hints' ), 10, 2 );
	}

	/*
	 * Settings Interface
	 */

	/**
	 * Accessor for a plugin setting with conditions for defaults and upgrades.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_setting( string $key, $default = '' ) {
		$settings = $this->get_settings();

		$value = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;

		if ( 'rewrite-urls' == $key && ! isset( $settings[ $key ] ) ) {
			$value = false;
		}

		if ( 'force-https' == $key && ! isset( $settings[ $key ] ) ) {
			$value = false;
		}

		return apply_filters( 'as3cf_assets_pull_setting_' . $key, $value );
	}

	/**
	 * Allowed settings keys for this plugin.
	 *
	 * @param bool $include_legacy Should legacy keys be included? Optional, default false.
	 *
	 * @return array
	 */
	public function get_allowed_settings_keys( bool $include_legacy = false ): array {
		return array(
			'rewrite-urls',
			'domain',
			'force-https',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_sensitive_settings(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_monitored_settings_blacklist(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_skip_sanitize_settings(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_path_format_settings(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_prefix_format_settings(): array {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_boolean_format_settings(): array {
		return array(
			'rewrite-urls',
			'force-https',
		);
	}

	/*
	 * UI
	 */

	/**
	 * Add additional translated strings for integration.
	 *
	 * @param array $strings
	 *
	 * @return array
	 */
	public function add_js_strings( array $strings ): array {
		return array_merge( $strings, array(
			'assets_panel_header'         => _x( 'Assets Pull', 'Assets panel title', 'amazon-s3-and-cloudfront' ),
			'assets_panel_header_details' => _x( 'Deliver scripts, styles, fonts and other assets from a content delivery network.', 'Assets panel details', 'amazon-s3-and-cloudfront' ),
			'assets_rewrite_urls'         => _x( 'Rewrite Asset URLs', 'Setting title', 'amazon-s3-and-cloudfront' ),
			'assets_rewrite_urls_desc'    => _x( 'Change the URLs of any enqueued asset files to use a CDN domain.', 'Setting description', 'amazon-s3-and-cloudfront' ),
			'assets_force_https'          => _x( 'Force HTTPS', 'Setting title', 'amazon-s3-and-cloudfront' ),
			'assets_force_https_desc'     => _x( 'Uses HTTPS for every rewritten asset URL instead of using the scheme of the current page.', 'Setting description', 'amazon-s3-and-cloudfront' ),
			'assets_domain_same_as_site'  => __( "Domain cannot be the same as the site's domain; use a subdomain instead.", 'amazon-s3-and-cloudfront' ),
		) );
	}

	/**
	 * Add additional config for integration.
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function add_js_config( array $config ): array {
		return array_merge(
			$config,
			$this->as3cf->get_api_manager()->get_api_endpoint(
				Assets_Settings::name()
			)->common_response()
		);
	}

	/**
	 * Add doc links for this integration.
	 *
	 * @handles as3cf_docs_data
	 *
	 * @param array $docs_data
	 *
	 * @return array
	 */
	public function get_docs( array $docs_data ): array {
		$docs_data['assets-pull'] = array(
			'url'  => $this->as3cf::dbrains_url( '/wp-offload-media/doc/assets-quick-start-guide', array( 'utm_campaign' => 'WP+Offload+S3', 'assets+doc' => 'assets-tab' ) ),
			'desc' => _x( 'Click to view help doc on our site', 'Help icon alt text', 'amazon-s3-and-cloudfront' ),
		);

		return $docs_data;
	}

	/*
	 * REST-API
	 */

	/**
	 * Add API endpoints.
	 *
	 * @handles as3cf_api_endpoints
	 *
	 * @param array $api_endpoints
	 *
	 * @return array
	 */
	public function add_api_endpoints( array $api_endpoints ): array {
		return array_merge( $api_endpoints, array(
			Assets_Domain_Check::name() => new Assets_Domain_Check( $this->as3cf ),
			Assets_Settings::name()     => new Assets_Settings( $this->as3cf ),
		) );
	}

	/**
	 * Add additional config into state API responses.
	 *
	 * @param array $response
	 *
	 * @return array
	 */
	public function api_get_state( array $response ): array {
		return array_merge(
			$response,
			$this->as3cf->get_api_manager()->get_api_endpoint(
				Assets_Settings::name()
			)->common_response()
		);
	}

	/*
	 * Support
	 */

	/**
	 * Integration specific diagnostic info.
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function diagnostic_info( string $output = '' ): string {
		$output .= 'Assets Pull:';
		$output .= "\r\n";

		$output .= 'Rewrite URLs: ';
		$output .= $this->on_off( 'rewrite-urls' );
		$output .= "\r\n";

		$output .= 'Domain: ';
		$output .= esc_html( $this->get_setting( 'domain' ) );
		$output .= "\r\n";

		$output .= 'Force HTTPS: ';
		$output .= $this->on_off( 'force-https' );
		$output .= "\r\n";

		$output .= 'Domain Check: ';
		$output .= $this->diagnostic_domain_check();
		$output .= "\r\n\r\n";

		$output .= 'AS3CF_ASSETS_PULL_SETTINGS: ';

		$settings_constant = static::settings_constant();

		if ( $settings_constant ) {
			$output .= 'Defined';

			if ( 'AS3CF_ASSETS_PULL_SETTINGS' !== $settings_constant ) {
				$output .= ' (using ' . $settings_constant . ')';
			}

			$defined_settings = $this->get_defined_settings();
			if ( empty( $defined_settings ) ) {
				$output .= ' - *EMPTY*';
			} else {
				$output .= "\r\n";
				$output .= 'AS3CF_ASSETS_PULL_SETTINGS Keys: ' . implode( ', ', array_keys( $defined_settings ) );
			}
		} else {
			$output .= 'Not defined';
		}
		$output .= "\r\n";

		return $output;
	}

	/**
	 * Run a domain check on the configured domain for the diagnostic information.
	 *
	 * @return string
	 */
	protected function diagnostic_domain_check(): string {
		$domain = $this->get_setting( 'domain' );

		if ( empty( $domain ) ) {
			return '(no domain)';
		}

		$check = new Domain_Check( $domain );

		try {
			$this->run_domain_check( $check );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return 'OK';
	}

	/*
	 * Domain Check
	 */

	/**
	 * Check given domain is configured correctly.
	 *
	 * @param string $domain
	 *
	 * @return array
	 */
	public function check_domain( string $domain ): array {
		$check = new Domain_Check( $domain );

		try {
			$this->run_domain_check( $check );
		} catch ( Exception $e ) {
			return array(
				'success'   => false,
				'domain'    => $check->domain(),
				'message'   => _x( 'Assets cannot be delivered from the CDN.', 'Assets domain check error', 'amazon-s3-and-cloudfront' ),
				'error'     => $e->getMessage(),
				'link'      => $this->domain_check_more_info_link( $e ),
				'timestamp' => current_time( 'timestamp' ),
			);
		}

		return array(
			'success'   => true,
			'domain'    => $check->domain(),
			'message'   => _x( 'Assets are serving from the CDN with the configured domain name.', 'Assets domain check success for active domain', 'amazon-s3-and-cloudfront' ),
			'timestamp' => current_time( 'timestamp' ),
		);
	}

	/**
	 * Build a "More info" link for a domain check error message.
	 *
	 * @param string|Exception $message
	 *
	 * @return string
	 */
	protected function domain_check_more_info_link( $message ): string {
		$exception = $message;

		if ( $message instanceof Exception ) {
			$message = $exception->getMessage();
		}

		$utm_content = 'assets+domain+check';

		if ( $exception instanceof Domain_Check_Exception ) {
			return $this->as3cf->more_info_link( $exception->more_info(), $utm_content, $exception->get_key() );
		}

		// Fall-back to a search of the docs.
		return $this->as3cf->more_info_link( '/wp-offload-media/docs/?swpquery=' . urlencode( $message ), $utm_content );
	}

	/**
	 * Execute the given domain check.
	 *
	 * @param Domain_Check $check
	 *
	 * @throws Exception
	 */
	protected function run_domain_check( Domain_Check $check ) {
		$test_time = microtime();
		$test_key  = base64_encode( $test_time ); //phpcs:ignore

		$this->test_assets_endpoint( $check, $test_key, $test_time );
	}

	/**
	 * Send a request to the test endpoint and make assertions about the response.
	 *
	 * @param Domain_Check $check
	 * @param string       $key
	 * @param string       $ver
	 *
	 * @throws Exception
	 */
	protected function test_assets_endpoint( Domain_Check $check, string $key, string $ver ) {
		/** @var Assets_Domain_Check $domain_check */
		$domain_check = $this->as3cf->get_api_manager()->get_api_endpoint(
			Assets_Domain_Check::name()
		);

		$test_endpoint = $domain_check->get_url( $key );
		$test_endpoint = add_query_arg( compact( 'ver' ), $test_endpoint );
		$test_endpoint = $this->rewrite_url( $test_endpoint );

		$response = $check->test_rest_endpoint( $test_endpoint );

		$expected = new Domain_Check_Response( compact( 'key', 'ver' ) );
		$expected->verify_signature( wp_remote_retrieve_header( $response, 'x-as3cf-signature' ) );
	}

	/*
	 * URL Rewriting
	 */

	/**
	 * Should asset URL rewriting be performed?
	 *
	 * @return bool
	 */
	public function should_rewrite_urls(): bool {
		// TODO: cache result and reuse.

		if ( ! $this->get_setting( 'rewrite-urls' ) ) {
			return false;
		}

		if (
			! Domain_Check::is_valid( $this->get_setting( 'domain' ) ) ||
			$this->as3cf->validation_manager->section_has_error( self::VALIDATOR_KEY )
		) {
			return false;
		}

		if ( is_admin() && ! AS3CF_Utils::is_ajax() && ! AS3CF_Utils::is_rest_api() ) {
			/**
			 * If you're really brave, you can have Assets Pull also rewrite enqueued assets
			 * within the WordPress admin dashboard.
			 *
			 * @param bool $rewrite
			 */
			return apply_filters( 'as3cf_assets_enable_wp_admin_rewrite', false );
		}

		return true;
	}

	/**
	 * Should the given asset URL be rewritten?
	 *
	 * @param mixed       $src    The asset URL to be rewritten.
	 * @param string|null $handle The asset's registered handle in the WordPress enqueue system.
	 *
	 * @return bool
	 */
	public static function should_rewrite_src( $src, string $handle = null ): bool {
		// If there is no string to rewrite, the answer is definitely no.
		if ( empty( $src ) || ! is_string( $src ) ) {
			return false;
		}

		if ( AS3CF_Utils::is_relative_url( $src ) ) {
			$rewrite = true;
		} elseif ( AS3CF_Utils::url_domains_match( $src, home_url() ) ) {
			$rewrite = true;
		} else {
			$rewrite = false;
		}

		/**
		 * @param bool        $rewrite Should the src be rewritten?
		 * @param string      $src     The asset URL to be rewritten.
		 * @param string|null $handle  The asset's registered handle in the WordPress enqueue system.
		 */
		return apply_filters( 'as3cf_assets_should_rewrite_src', $rewrite, $src, $handle );
	}

	/**
	 * Rewrite an asset's src.
	 *
	 * @param mixed       $src
	 * @param string|null $handle
	 *
	 * @return mixed
	 */
	public function rewrite_src( $src, string $handle = null ) {
		if ( empty( $src ) || ! is_string( $src ) ) {
			return $src;
		}

		if ( ! $this->should_rewrite_urls() ) {
			return $src;
		}

		if ( ! static::should_rewrite_src( $src, $handle ) ) {
			return $src;
		}

		return $this->rewrite_url( $src, $handle );
	}

	/**
	 * Rewrite a URL to use the asset's domain and scheme.
	 *
	 * @param string      $url
	 * @param string|null $handle
	 *
	 * @return string
	 */
	protected function rewrite_url( string $url, string $handle = null ): string {
		$rewritten = 'http://' . $this->get_setting( 'domain' );
		$rewritten .= AS3CF_Utils::parse_url( $url, PHP_URL_PATH );
		$query     = AS3CF_Utils::parse_url( $url, PHP_URL_QUERY );
		$rewritten .= $query ? ( '?' . $query ) : '';
		$scheme    = $this->get_setting( 'force-https' ) ? 'https' : null;

		/**
		 * Adjust the URL scheme that Assets Pull is going to use for rewritten URL.
		 *
		 * @param string|null $scheme
		 * @param string      $url
		 * @param string      $handle
		 */
		$scheme = apply_filters( 'as3cf_assets_pull_scheme', $scheme, $url, $handle );

		return set_url_scheme( $rewritten, $scheme );
	}

	/**
	 * Register a DNS prefetch tag for the pull domain if rewriting is enabled.
	 *
	 * @param array  $hints
	 * @param string $relation_type
	 *
	 * @return array
	 */
	public function register_resource_hints( array $hints, string $relation_type ): array {
		if ( 'dns-prefetch' === $relation_type && $this->should_rewrite_urls() ) {
			$hints[] = '//' . $this->get_setting( 'domain' );
		}

		return $hints;
	}

	/**
	 * Validate settings for Assets.
	 *
	 * @param bool $force Force time resource consuming or state altering tests to run.
	 *
	 * @return AS3CF_Result
	 */
	public function validate_settings( bool $force = false ): AS3CF_Result {
		if ( $this->get_setting( 'rewrite-urls' ) ) {
			$domain_check_result = $this->check_domain( $this->get_setting( 'domain' ) );

			// Did the domain check fail?
			if ( ! $domain_check_result['success'] ) {
				return new AS3CF_Result(
					Validator_Interface::AS3CF_STATUS_MESSAGE_ERROR,
					sprintf(
						_x( '%1$s %2$s In the meantime local assets are being served. %3$s', 'Assets notice for domain issue', 'amazon-s3-and-cloudfront' ),
						$domain_check_result['message'],
						$domain_check_result['error'],
						$domain_check_result['link']
					)
				);
			}

			// All good.
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_SUCCESS,
				$domain_check_result['message']
			);
		} else {
			return new AS3CF_Result(
				Validator_Interface::AS3CF_STATUS_MESSAGE_WARNING,
				sprintf(
					__(
						'Assets cannot be delivered from the CDN until <strong>Rewrite Asset URLs</strong> is enabled. In the meantime, local assets are being served. <a href="%1$s" target="_blank">View Assets Quick Start Guide</a>',
						'amazon-s3-and-cloudfront'
					),
					$this->as3cf::dbrains_url( '/wp-offload-media/doc/assets-quick-start-guide', array( 'utm_campaign' => 'WP+Offload+S3', 'assets+doc' => 'assets-tab' ) )
				)
			);
		}
	}

	/**
	 * Get the name of the actions that are fired when the settings that the validator
	 * is responsible for are saved.
	 *
	 * @return array
	 */
	public function post_save_settings_actions(): array {
		return array( 'as3cf_post_save_assets_settings' );
	}
}
