<?php

use DeliciousBrains\WP_Offload_Media\API\V1\Settings;
use DeliciousBrains\WP_Offload_Media\API\V1\State;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Item_Handler;
use DeliciousBrains\WP_Offload_Media\Pro\API\V1\Licences;
use DeliciousBrains\WP_Offload_Media\Pro\API\V1\Tools;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Assets\Assets as Assets_Integration;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss\BuddyBoss;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Core_Pro as Core_Pro_Integration;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Divi;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Easy_Digital_Downloads;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Elementor;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Enable_Media_Replace;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Media_Library_Pro as Media_Library_Pro_Integration;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Meta_Slider;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Woocommerce;
use DeliciousBrains\WP_Offload_Media\Pro\Integrations\Wpml;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Remove_Provider_Handler;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Update_Acl_Handler;
use DeliciousBrains\WP_Offload_Media\Pro\Tools_Manager;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Add_Metadata;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair\Reverse_Add_Metadata;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair\Verify_Add_Metadata;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Copy_Buckets;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Download_And_Remover;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Downloader;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Elementor_Analyze_And_Repair;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Move_Objects;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Move_Private_Objects;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Move_Public_Objects;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Remove_Local_Files;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Update_ACLs;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Uploader;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Woocommerce_Product_Urls;
use DeliciousBrains\WP_Offload_Media\Pro\Upgrades\Disable_Compatibility_Plugins;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider;

class Amazon_S3_And_CloudFront_Pro extends Amazon_S3_And_CloudFront {

	/**
	 * @var AS3CF_Pro_Licences_Updates
	 */
	protected $licence;

	/**
	 * @var Tools_Manager
	 */
	protected $tools;

	/**
	 * @var array
	 */
	private $_is_pro_plugin_setup; // @phpcs:ignore

	/**
	 * @param string $plugin_file_path
	 *
	 * @throws Exception
	 */
	public function __construct( $plugin_file_path ) {
		$this->tools = Tools_Manager::get_instance( $this );

		parent::__construct( $plugin_file_path, $this->plugin_slug );
	}

	/**
	 * Plugin initialization
	 *
	 * @param string $plugin_file_path
	 *
	 * @throws Exception
	 */
	public function init( $plugin_file_path ) {
		// Pro delivery providers
		add_filter( 'as3cf_delivery_provider_classes', array( $this, 'enable_delivery_providers' ) );

		parent::init( $plugin_file_path );

		$this->plugin_title      = __( 'WP Offload Media', 'amazon-s3-and-cloudfront' );
		$this->plugin_menu_title = __( 'WP Offload Media', 'amazon-s3-and-cloudfront' );

		// Licence and updates handler
		if ( is_admin() || AS3CF_Utils::is_rest_api() ) {
			$this->licence = new AS3CF_Pro_Licences_Updates( $this );
		}

		// add our custom CSS classes to <body>
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		// Only enable the plugin if compatible,
		// so we don't disable the license and updates functionality when disabled
		if ( self::is_compatible() ) {
			$this->enable_plugin();
		}
	}

	/**
	 * Enable the complete plugin when compatible
	 */
	public function enable_plugin() {
		// Add some extra information into some standard REST API responses.
		add_filter( $this->get_plugin_prefix() . '_api_response_get_' . Settings::name(), array( $this, 'api_get_settings' ) );
		add_filter( $this->get_plugin_prefix() . '_api_response_put_' . Settings::name(), array( $this, 'api_get_settings' ) );
		add_filter( $this->get_plugin_prefix() . '_api_response_get_' . State::name(), array( $this, 'api_get_state' ) );

		// Pro customisations
		add_filter( 'as3cf_lost_files_notice', array( $this, 'lost_files_notice' ) );

		// Settings link on the plugins page
		add_filter( 'plugin_action_links', array( $this, 'plugin_actions_settings_link' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_actions_settings_link' ), 10, 2 );

		// Diagnostic info
		add_filter( 'as3cf_diagnostic_info', array( $this, 'diagnostic_info' ) );

		// Register tools
		add_action( 'as3cf_pro_init', array( $this, 'register_tools' ) );

		// Add Pro integrations.
		add_filter( 'as3cf_integrations', array( $this, 'add_integrations' ) );

		// Add Pro API endpoints.
		add_filter( 'as3cf_api_endpoints', array( $this, 'add_api_endpoints' ) );

		// Include compatibility code for other plugins
		$this->plugin_compat = new AS3CF_Pro_Plugin_Compatibility( $this );

		// Perform network upgrades
		new Disable_Compatibility_Plugins( $this, $this->plugin_version );
	}

	/**
	 * Add Pro integrations.
	 *
	 * @handles as3cf_integrations
	 *
	 * @param array $integrations
	 *
	 * @return array
	 */
	public function add_integrations( array $integrations ): array {
		return array_merge( $integrations, array(
			'core'   => new Core_Pro_Integration( $this ),
			'mlib'   => new Media_Library_Pro_Integration( $this ),
			'assets' => new Assets_Integration( $this ),
			'divi'   => new Divi( $this ),
			'edd'    => new Easy_Digital_Downloads( $this ),
			'emr'    => new Enable_Media_Replace( $this ),
			'msl'    => new Meta_Slider( $this ),
			'woo'    => new Woocommerce( $this ),
			'wpml'   => new Wpml( $this ),
			'elem'   => new Elementor( $this ),
			'bboss'  => new BuddyBoss( $this ),
		) );
	}

	/**
	 * Add Pro API endpoints.
	 *
	 * @handles as3cf_api_endpoints
	 *
	 * @param array $api_endpoints
	 *
	 * @return array
	 */
	public function add_api_endpoints( array $api_endpoints ): array {
		return array_merge( $api_endpoints, array(
			Licences::name() => new Licences( $this ),
			Tools::name()    => new Tools( $this ),
		) );
	}

	/**
	 * Enable Pro delivery providers.
	 *
	 * @param array $delivery_providers
	 *
	 * @return array
	 */
	public function enable_delivery_providers( $delivery_providers ) {
		$delivery_providers[ AWS_CloudFront::get_provider_key_name() ] = 'DeliciousBrains\WP_Offload_Media\Pro\Providers\Delivery\AWS_CloudFront_Pro';

		return $delivery_providers;
	}

	/**
	 * Register tools once everything is setup and ready to go.
	 *
	 * @handles as3cf_pro_init
	 *
	 * @param Amazon_S3_And_CloudFront_Pro $as3cfpro
	 */
	public function register_tools( Amazon_S3_And_CloudFront_Pro $as3cfpro ) {
		if ( ! is_admin() && ! wp_doing_cron() && ! AS3CF_Utils::is_rest_api() ) {
			return;
		}

		$this->tools->register_tool( new Uploader( $as3cfpro ) );
		$this->tools->register_tool( new Downloader( $as3cfpro ) );
		$this->tools->register_tool( new Download_And_Remover( $as3cfpro ) );
		$this->tools->register_tool( new Remove_Local_Files( $as3cfpro ) );
		$this->tools->register_tool( new Copy_Buckets( $as3cfpro ) );
		$this->tools->register_tool( new Move_Objects( $as3cfpro ) );
		$this->tools->register_tool( new Move_Public_Objects( $as3cfpro ) );
		$this->tools->register_tool( new Move_Private_Objects( $as3cfpro ) );
		$this->tools->register_tool( new Update_ACLs( $as3cfpro ) );
		$this->tools->register_tool( new Add_Metadata( $as3cfpro ) );
		$this->tools->register_tool( new Reverse_Add_Metadata( $as3cfpro ) );
		$this->tools->register_tool( new Verify_Add_Metadata( $as3cfpro ) );
		$this->tools->register_tool( new Woocommerce_Product_Urls( $as3cfpro ) );
		$this->tools->register_tool( new Elementor_Analyze_And_Repair( $as3cfpro ) );
	}

	/**
	 * Is this plugin compatible with its required plugin?
	 *
	 * @return bool
	 */
	public static function is_compatible() {
		global $as3cf_compat_check;

		return $as3cf_compat_check->is_compatible();
	}

	/**
	 * Enqueue assets needed for settings UI.
	 *
	 * @param array $config Initial settings.
	 */
	protected function load_settings_assets( $config = array() ) {
		$this->enqueue_style( 'as3cf-pro-settings', 'assets/css/pro/settings' );
		$this->enqueue_script( 'as3cf-pro-settings', 'assets/js/pro/settings', array(), false );

		$config['settings']                     = apply_filters( 'as3cfpro_js_settings', $config['settings'] );
		$config['strings']                      = apply_filters( 'as3cfpro_js_strings', $config['strings'] );
		$config['licences']                     = $this->get_licences();
		$config['offload_remaining_with_count'] = $this->get_offload_remaining_with_count_message();
		$config['documentation']                = $this->get_documentation();
		$config['tools']                        = $this->get_tools_info();

		$config = apply_filters( 'as3cfpro_js_config', $config );

		wp_localize_script( 'as3cf-pro-settings',
			'as3cfpro_settings',
			$config
		);
	}

	/**
	 * Add custom classes to the HTML body tag
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( ! $classes ) {
			$classes = array();
		} else {
			$classes = explode( ' ', $classes );
		}

		$classes[] = 'as3cf-pro';

		// Recommended way to target WP 3.8+
		// http://make.wordpress.org/ui/2013/11/19/targeting-the-new-dashboard-design-in-a-post-mp6-world/
		if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ) {
			if ( ! in_array( 'mp6', $classes ) ) {
				$classes[] = 'mp6';
			}
		}

		return implode( ' ', $classes );
	}

	/**
	 * Allowed settings keys for this plugin.
	 *
	 * @param bool $include_legacy Should legacy keys be included? Optional, default false.
	 *
	 * @return array
	 */
	public function get_allowed_settings_keys( bool $include_legacy = false ): array {
		return array_merge(
			parent::get_allowed_settings_keys( $include_legacy ),
			array( 'licence' )
		);
	}

	/**
	 * Add license info into settings API responses.
	 *
	 * @param array $response
	 *
	 * @return array
	 */
	public function api_get_settings( $response ) {
		if ( is_array( $response ) && isset( $response['settings'] ) ) {
			$response['licences']                     = $this->get_licences();
			$response['offload_remaining_with_count'] = $this->get_offload_remaining_with_count_message();
		}

		return $response;
	}

	/**
	 * Add license info into state API responses.
	 *
	 * @param array $response
	 *
	 * @return array
	 */
	public function api_get_state( $response ) {
		if ( is_array( $response ) && isset( $response['counts'] ) ) {
			$response['licences']                     = $this->get_licences();
			$response['offload_remaining_with_count'] = $this->get_offload_remaining_with_count_message();
		}

		return $response;
	}

	/**
	 * Add bulk action explanation to lost files notice
	 *
	 * @param string $notice
	 *
	 * @return string
	 */
	public function lost_files_notice( $notice ) {
		return $notice . ' ' . __( 'Alternatively, use the Media Library bulk action <strong>Copy to Server from Bucket</strong> to ensure the local files exist.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Render a view template file specific to child class
	 * or use parent view as a fallback
	 *
	 * @param string $view View filename without the extension
	 * @param array  $args Arguments to pass to the view
	 */
	public function render_view( $view, $args = array() ) {
		extract( $args );
		$view_file = $this->plugin_dir_path . '/view/pro/' . $view . '.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			include $this->plugin_dir_path . '/view/' . $view . '.php';
		}
	}

	/**
	 * Get all the blogs of the site (only one if single site)
	 * Returning    - table prefix
	 *              - last_attachment: flag to record if we have processed all attachments for the blog
	 *              - processed: record last post id process to be used as an offset in the next batch for the blog
	 *
	 * @return array
	 */
	public function get_blogs_data() {
		global $wpdb;

		$blogs = array();

		$blogs[1] = array(
			'prefix' => $wpdb->prefix,
		);

		if ( is_multisite() ) {
			$blog_ids = AS3CF_Utils::get_blog_ids();

			foreach ( $blog_ids as $blog_id ) {
				$blogs[ $blog_id ] = array(
					'prefix' => $wpdb->get_blog_prefix( $blog_id ),
				);
			}
		}

		return $blogs;
	}

	/**
	 * Calculate batch limit based on the amount of registered image sizes
	 *
	 * @param int         $max
	 * @param string|null $filter_handle
	 *
	 * @return float
	 */
	public function get_batch_limit( $max, $filter_handle = null ) {
		if ( ! is_null( $filter_handle ) ) {
			$max = apply_filters( $filter_handle, $max );
		}

		$sizes = count( get_intermediate_image_sizes() );

		return floor( $max / $sizes );
	}

	/**
	 * Get the plugin slug used as the identifier in the Plugin page table
	 *
	 * @return string
	 */
	public function get_plugin_row_slug() {
		return sanitize_title( $this->licence->plugin->name );
	}

	/**
	 * Checks whether the saved licence has expired or not.
	 * Interfaces to the $licence object instead of making it public.
	 *
	 * @param bool  $skip_transient_check
	 * @param bool  $skip_expired_check
	 * @param array $licence_response Optional pre-fetched licence response data.
	 *
	 * @return bool
	 */
	public function is_valid_licence( $skip_transient_check = false, $skip_expired_check = true, $licence_response = array() ) {
		return $this->licence->is_valid_licence( $skip_transient_check, $skip_expired_check, $licence_response );
	}

	/**
	 * Check if the license is over the media limit.
	 *
	 * @param array $media_limit_response Optional pre-fetched media limit response data.
	 *
	 * @return bool
	 */
	public function is_licence_over_media_limit( $media_limit_response = array() ) {
		return $this->licence->is_licence_over_media_limit( $media_limit_response );
	}

	/**
	 * Update the API with the total of attachments offloaded to S3 for the site
	 */
	public function update_media_library_total() {
		$this->licence->check_licence_media_limit( true, true );
	}

	/**
	 * Get the total number of media items offloaded, the limit for the license,
	 * and whether the site counts towards the limit.
	 *
	 * @return array
	 */
	public function get_total_and_limit_for_licence(): array {
		if ( ! $this->licence->is_valid_licence() ) {
			return array();
		}

		$cached_media_limit_check = get_site_transient( $this->licence->plugin->prefix . '_licence_media_check' );

		$media_limit_check = $this->licence->check_licence_media_limit();

		if (
			! is_array( $media_limit_check ) ||
			! isset( $media_limit_check['total'] ) ||
			! isset( $media_limit_check['limit'] )
		) {
			// Can't use latest API call.
			if (
				! is_array( $cached_media_limit_check ) ||
				! isset( $cached_media_limit_check['total'] ) ||
				! isset( $cached_media_limit_check['limit'] )
			) {
				// Cached data failed
				return array();
			}

			// Use cached data
			$media_limit_check = $cached_media_limit_check;
		}

		return array(
			'total'               => absint( $media_limit_check['total'] ),
			'limit'               => absint( $media_limit_check['limit'] ),
			'counts_toward_limit' => $this->licence->counts_toward_limit( $media_limit_check ),
		);
	}

	/**
	 * Get the number of media items allowed to be uploaded for the license.
	 *
	 * @return int
	 */
	public function get_total_allowed_media_items_to_upload(): int {
		$result = $this->get_total_and_limit_for_licence();

		if ( empty( $result ) ) {
			return 0;
		}

		if ( ! isset( $result['total'] ) || ! isset( $result['limit'] ) || ! isset( $result['counts_toward_limit'] ) ) {
			// Something weird is going on, bail.
			return 0;
		}

		if ( empty( $result['limit'] ) || false === $result['counts_toward_limit'] ) {
			// No limit.
			return -1;
		}

		$allowed = $result['limit'] - $result['total'];

		if ( $allowed < 0 ) {
			// Upload limit exceeded.
			return 0;
		}

		return $allowed;
	}

	/**
	 * Get the addons for the plugin with license information
	 *
	 * @return array
	 */
	public function get_plugin_addons() {
		return $this->licence->addons;
	}

	/**
	 * Check to see if the plugin is set up.
	 *
	 * @param bool $with_credentials Do S3 credentials need to be set up too? Defaults to false.
	 *
	 * @return bool
	 */
	public function is_pro_plugin_setup( $with_credentials = false ) {
		if ( ! is_null( $this->_is_pro_plugin_setup ) && isset( $this->_is_pro_plugin_setup[ $with_credentials ] ) ) {
			return $this->_is_pro_plugin_setup[ $with_credentials ];
		}

		if ( isset( $this->licence ) ) {
			// If no license set, then Pro isn't set up.
			if ( empty( $this->licence->get_licence_key() ) ) {
				$this->_is_pro_plugin_setup[ $with_credentials ] = false;

				// Ensure notice regarding license response issues is removed if there is no license.
				$this->notices->remove_notice_by_id( 'as3cfpro-licence-response-missing' );

				return $this->_is_pro_plugin_setup[ $with_credentials ];
			}

			// If there hasn't yet been a check of the licence, or it is not valid, Pro setup is not complete.
			$licence_response = json_decode( get_site_transient( $this->licence->plugin->prefix . '_licence_response' ), true );
			if ( empty( $licence_response ) || ! is_array( $licence_response ) || ! $this->is_valid_licence( false, true, $licence_response ) ) {
				// Empty or invalid license.
				$this->_is_pro_plugin_setup[ $with_credentials ] = false;

				// If we really should have had a license response, make sure user knows there's a problem.
				if ( empty( $licence_response ) || ! is_array( $licence_response ) ) {
					// Although the default, only_show_to_user included here to telegraph the fact this mode is used
					// as it is less likely to be buggy compared to site transients, and therefore more likely to be seen.
					// It's also a fraction faster for remove_notice_by_id as user meta is checked before site options.
					$doc_url = $this->dbrains_url(
						'/wp-offload-media/doc/non-essential-features/',
						array(
							'utm_campaign' => 'support+docs',
							'utm_content'  => 'license-response-missing',
						)
					);

					$mesg = __( 'A problem occurred when trying to validate your license, therefore your <a href="%1$s">Pro features</a> have been disabled.', 'amazon-s3-and-cloudfront' );
					$mesg = sprintf( $mesg, $doc_url );
					$mesg .= '&nbsp;';
					$mesg .= $this->more_info_link( '/wp-offload-media/doc/pro-features-disabled-notice/', 'license-response-missing' );
				} elseif ( ! empty( $licence_response['errors'] ) ) {
					$mesg = $this->licence->get_licence_status_message( $licence_response );
				}

				if ( ! empty( $mesg ) ) {
					$this->notices->add_notice(
						$mesg,
						array(
							'type'                  => 'error',
							'custom_id'             => 'as3cfpro-licence-response-missing',
							'dismissible'           => false,
							'only_show_to_user'     => true,
							'only_show_in_settings' => true,
						)
					);
				}

				return $this->_is_pro_plugin_setup[ $with_credentials ];
			}

			// Ensure notice regarding license response issues is removed if things look ok.
			$this->notices->remove_notice_by_id( 'as3cfpro-licence-response-missing' );

			// If licence is looking good, then we're only concerned if licence is over limit.
			$media_limit_response = get_site_transient( $this->licence->plugin->prefix . '_licence_media_check' );
			if ( ! empty( $media_limit_response ) && is_array( $media_limit_response ) && $this->is_licence_over_media_limit( $media_limit_response ) ) {
				// License key over the media library total license limit
				$this->_is_pro_plugin_setup[ $with_credentials ] = false;

				return $this->_is_pro_plugin_setup[ $with_credentials ];
			}
		}

		$this->_is_pro_plugin_setup[ $with_credentials ] = $this->is_plugin_setup( $with_credentials );

		return $this->_is_pro_plugin_setup[ $with_credentials ];
	}

	/**
	 * Pro specific diagnostic info
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function diagnostic_info( $output = '' ) {
		$post_count = $this->get_diagnostic_post_count();
		$output     .= 'Posts Count: ';
		$output     .= number_format_i18n( $post_count );
		$output     .= "\r\n\r\n";

		$output .= 'Pro Upgrade: ';
		$output .= "\r\n";
		$output .= 'License Status: ';
		$output .= $this->licence->licence_status_description();
		$output .= "\r\n";
		$output .= 'License Constant: ';
		$output .= $this->licence->is_licence_constant() ? 'On' : 'Off';
		$output .= "\r\n\r\n";

		$output .= 'Host IP: ';
		$output .= gethostbyname( parse_url( admin_url(), PHP_URL_HOST ) );
		$output .= "\r\n";

		// Background processing jobs
		$output   .= 'Background Jobs: ';
		$job_keys = AS3CF_Pro_Utils::get_batch_job_keys();

		global $wpdb;
		$table        = $wpdb->options;
		$column       = 'option_name';
		$value_column = 'option_value';

		if ( is_multisite() ) {
			$table        = $wpdb->sitemeta;
			$column       = 'meta_key';
			$value_column = 'meta_value';
		}

		foreach ( $job_keys as $key ) {
			$jobs = $wpdb->get_results( $wpdb->prepare( "
				SELECT * FROM {$table}
				WHERE {$column} LIKE %s
			", $key ) );

			if ( empty( $jobs ) ) {
				continue;
			}

			foreach ( $jobs as $job ) {
				$output .= $job->{$column};
				$output .= "\r\n";
				$output .= print_r( maybe_unserialize( $job->{$value_column} ), true ); // phpcs:ignore
				$output .= "\r\n";
			}
		}

		$output .= "\r\n\r\n";

		return $output;
	}

	/**
	 * Get the total of posts (in scope for find and replace) for the diagnostic log
	 *
	 * @return int
	 */
	protected function get_diagnostic_post_count() {
		if ( false === ( $post_count = get_site_transient( 'as3cf_post_count' ) ) ) {
			global $wpdb;

			$post_count     = 0;
			$table_prefixes = AS3CF_Utils::get_all_blog_table_prefixes();

			foreach ( $table_prefixes as $table_prefix ) {
				$post_count += $wpdb->get_var( "SELECT COUNT(ID) FROM {$table_prefix}posts" );
			}

			set_site_transient( 'as3cf_post_count', $post_count, 2 * HOUR_IN_SECONDS );
		}

		return $post_count;
	}

	/**
	 * Get object keys that exist on provider for items.
	 *
	 * It's possible that items belong to different buckets therefore they could have
	 * different regions, so we have to build an array of clients and commands.
	 *
	 * @param array  $source_ids
	 * @param string $source_type
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_provider_keys( $source_ids, $source_type = 'media-library' ) {
		$regions           = array();
		$originals         = array();
		$private_regions   = array();
		$private_originals = array();

		/** @var Item $class */
		$class = $this->get_source_type_class( $source_type );

		foreach ( $source_ids as $source_id ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( ! $as3cf_item->served_by_provider( true ) ) {
				continue;
			}

			if ( ! isset( $regions[ $as3cf_item->region() ]['provider_client'] ) ) {
				$regions[ $as3cf_item->region() ]['provider_client'] = $this->get_provider_client( $as3cf_item->region(), true );
			}

			$regions[ $as3cf_item->region() ]['locations'][ $source_id ] = array(
				'Bucket' => $as3cf_item->bucket(),
				'Prefix' => AS3CF_Utils::strip_image_edit_suffix_and_extension( $as3cf_item->path(), $source_type ),
			);

			if ( ! empty( $as3cf_item->private_prefix() ) ) {
				if ( ! isset( $private_regions[ $as3cf_item->region() ]['provider_client'] ) ) {
					$private_regions[ $as3cf_item->region() ]['provider_client'] = $this->get_provider_client( $as3cf_item->region(), true );
				}

				$private_regions[ $as3cf_item->region() ]['locations'][ $source_id ] = array(
					'Bucket' => $as3cf_item->bucket(),
					'Prefix' => AS3CF_Utils::strip_image_edit_suffix_and_extension( $as3cf_item->private_prefix() . $as3cf_item->path(), $source_type ),
				);
			}

			if ( wp_basename( $as3cf_item->original_path() ) !== wp_basename( $as3cf_item->path() ) ) {
				if ( ! isset( $originals[ $as3cf_item->region() ]['provider_client'] ) ) {
					$originals[ $as3cf_item->region() ]['provider_client'] = $this->get_provider_client( $as3cf_item->region(), true );
				}

				$originals[ $as3cf_item->region() ]['locations'][ $source_id ] = array(
					'Bucket' => $as3cf_item->bucket(),
					'Prefix' => AS3CF_Utils::strip_image_edit_suffix_and_extension( $as3cf_item->original_path(), $source_type ),
				);

				if ( ! empty( $as3cf_item->private_prefix() ) ) {
					if ( ! isset( $private_originals[ $as3cf_item->region() ]['provider_client'] ) ) {
						$private_originals[ $as3cf_item->region() ]['provider_client'] = $this->get_provider_client( $as3cf_item->region(), true );
					}

					$private_originals[ $as3cf_item->region() ]['locations'][ $source_id ] = array(
						'Bucket' => $as3cf_item->bucket(),
						'Prefix' => AS3CF_Utils::strip_image_edit_suffix_and_extension( $as3cf_item->private_prefix() . $as3cf_item->original_path(), $source_type ),
					);
				}
			}
		}

		$keys = $this->consolidate_provider_keys_from_regions( array(), $regions, $source_type );
		$keys = $this->consolidate_provider_keys_from_regions( $keys, $private_regions, $source_type );
		$keys = $this->consolidate_provider_keys_from_regions( $keys, $originals, $source_type );
		$keys = $this->consolidate_provider_keys_from_regions( $keys, $private_originals, $source_type );

		return $keys;
	}

	/**
	 * Get keys for region's locations and consolidate with keys from previous use.
	 *
	 * Note: Very much a helper function for `get_provider_keys`.
	 *
	 * @param array  $keys
	 * @param array  $regions
	 * @param string $source_type
	 *
	 * @return array
	 */
	private function consolidate_provider_keys_from_regions( $keys, $regions, $source_type ) {
		if ( ! empty( $regions ) ) {
			$region_keys = Storage_Provider::get_keys_from_regions( $regions, $source_type );

			if ( ! empty( $region_keys ) ) {
				if ( empty( $keys ) ) {
					return $region_keys;
				}

				foreach ( $region_keys as $item_source_id => $paths ) {
					if ( ! empty( $keys[ $item_source_id ] ) ) {
						$keys[ $item_source_id ] = array_unique( array_merge( $keys[ $item_source_id ], $paths ) );
					} else {
						$keys[ $item_source_id ] = $paths;
					}
				}
			}
		}

		return $keys;
	}

	/**
	 * Returns the Item_Handler instance for the given handler type.
	 *
	 * @param string $handler_type
	 *
	 * @return Item_Handler
	 */
	public function get_item_handler( $handler_type ) {
		if ( isset( $this->item_handlers[ $handler_type ] ) ) {
			return $this->item_handlers[ $handler_type ];
		}

		switch ( $handler_type ) {
			case Remove_Provider_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Remove_Provider_Handler( $this );
				break;
			case Update_Acl_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Update_Acl_Handler( $this );
				break;
			default:
				return parent::get_item_handler( $handler_type );
		}

		return $this->item_handlers[ $handler_type ];
	}

	/**
	 * Get UTM source for plugin.
	 *
	 * @return string
	 */
	protected static function get_utm_source() {
		return 'OS3+Paid';
	}

	/**
	 * Get licence information that is safe to display.
	 *
	 * @param bool $skip_transient_check
	 *
	 * @return array
	 */
	public function get_licences( bool $skip_transient_check = false ): array {
		if ( empty( $this->licence ) ) {
			return array();
		}

		// We currently have one licence applied to a plugin install.
		return array( $this->licence->get_licence_info( $skip_transient_check ) );
	}

	/**
	 * Is the license key defined as a constant?
	 *
	 * @return bool
	 */
	public function is_licence_constant() {
		return $this->licence->is_licence_constant();
	}

	/**
	 * Activate a licence.
	 *
	 * @param string $licence_key
	 *
	 * @return bool|WP_Error
	 */
	public function activate_licence( $licence_key ) {
		return $this->licence->activate( $licence_key );
	}

	/**
	 * Remove the license.
	 */
	public function remove_licence() {
		$this->licence->remove();
	}

	/**
	 * Returns an array of title and url values to be used in documentation sidebar.
	 *
	 * @return array[]
	 */
	private function get_documentation() {
		$response = get_site_transient( $this->licence->plugin->prefix . '_licence_response' );

		if ( $response ) {
			$decoded = json_decode( $response, true );

			if ( ! empty( $decoded['documentation'] ) && is_array( $decoded['documentation'] ) ) {
				return $decoded['documentation'];
			}
		}

		// Fallback to hard-coded docs.
		return array(
			array(
				'title' => __( 'Getting Started', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/getting-started/' ),
			),
			array(
				'title' => __( 'How To', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/how-to/' ),
			),
			array(
				'title' => __( 'Addons', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/addons/' ),
			),
			array(
				'title' => __( 'Integrations', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/integrations/' ),
			),
			array(
				'title' => __( 'Changelogs', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/changelogs/' ),
			),
			array(
				'title' => __( 'Debugging', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/debugging/' ),
			),
			array(
				'title' => __( 'Common Errors', 'amazon-s3-and-cloudfront' ),
				'url'   => static::dbrains_url( '/wp-offload-media/docs/common-errors/' ),
			),
		);
	}

	/**
	 * Get info for all tools, including current status.
	 *
	 * @return array
	 */
	public function get_tools_info() {
		return $this->tools->get_tools_info();
	}

	/**
	 * Try and perform the requested action for a tool identified by its key.
	 *
	 * @param string $tool_key
	 * @param string $action
	 *
	 * @return bool
	 */
	public function perform_tool_action( $tool_key, $action ) {
		return $this->tools->perform_action( $tool_key, $action );
	}

	/**
	 * Dismiss one or all tool errors for a given tool.
	 *
	 * @param string     $tool_key
	 * @param int        $blog_id
	 * @param string     $source_type
	 * @param int        $source_id
	 * @param string|int $errors Optional indicator of which error to dismiss for source item, default 'all'.
	 *
	 * @return bool
	 */
	public function dismiss_tool_errors( $tool_key, $blog_id, $source_type, $source_id, $errors = 'all' ) {
		$tool = $this->tools->get_tool( $tool_key );

		if ( false === $tool ) {
			return false;
		}

		$tool->dismiss_errors( $blog_id, $source_type, $source_id, $errors );

		return true;
	}

	/**
	 * Get URLs needed by the frontend.
	 *
	 * @return array
	 */
	public function get_js_urls(): array {
		return apply_filters( 'as3cfpro_js_urls', parent::get_js_urls() );
	}

	/**
	 * Is the given feature enabled?
	 *
	 * @param string $feature
	 *
	 * @return bool
	 */
	public function feature_enabled( string $feature ): bool {
		$licences = $this->get_licences();

		// Fall-back to transient if licence property not yet initialized.
		if ( empty( $licences ) ) {
			$decoded_licence = json_decode( get_site_transient( 'as3cfpro_licence_response' ), true );
			$licences        = empty( $decoded_licence ) ? array() : array( $decoded_licence );
		}

		if ( empty( $licences ) ) {
			return false;
		}

		foreach ( $licences as $licence ) {
			// TODO: >>> Remove when https://github.com/deliciousbrains/site/issues/3287 implemented.
			if ( empty( $licence['features'] ) && ! empty( $licence['plan'] ) && ! in_array( $licence['plan'], array( 'Bronze', 'Silver' ) ) ) {
				$licence['features'][] = 'assets';
			}
			if ( empty( $licence['features'] ) && ! empty( $licence['licence_name'] ) && ! in_array( $licence['licence_name'], array( 'Bronze', 'Silver' ) ) ) {
				$licence['features'][] = 'assets';
			}
			// TODO: <<< Remove when https://github.com/deliciousbrains/site/issues/3287 implemented.

			if ( empty( $licence['features'] ) || ! is_array( $licence['features'] ) ) {
				continue;
			}

			if ( in_array( $feature, $licence['features'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a message promoting that there is media that could be offloaded.
	 *
	 * @return string
	 */
	public function get_offload_remaining_with_count_message(): string {
		$counts = $this->media_counts();

		if ( ! empty( $counts['not_offloaded'] ) && 1 === $counts['not_offloaded'] ) {
			return sprintf( _x( 'Offload Remaining %s Item', 'Button text (singular)', 'amazon-s3-and-cloudfront' ), number_format_i18n( $counts['not_offloaded'] ) );
		} elseif ( ! empty( $counts['not_offloaded'] ) && 0 < $counts['not_offloaded'] ) {
			return sprintf( _x( 'Offload Remaining %s Items', 'Button text (plural)', 'amazon-s3-and-cloudfront' ), number_format_i18n( $counts['not_offloaded'] ) );
		} else {
			return _x( 'Offload Remaining', 'Button text', 'amazon-s3-and-cloudfront' );
		}
	}
}
