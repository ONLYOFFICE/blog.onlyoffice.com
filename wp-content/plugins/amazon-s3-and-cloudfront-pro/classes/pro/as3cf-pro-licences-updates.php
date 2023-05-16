<?php
/**
 * AS3CF Pro Licences and Updates Class
 *
 * @package     amazon-s3-and-cloudfront-pro
 * @subpackage  licences
 * @copyright   Copyright (c) 2015, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AS3CF_Pro_Licences_Updates Class
 *
 * This class handles the licencing and plugin updates specific for the plugin
 * using the common Delicious Brains classes
 *
 * @since 1.0
 */
class AS3CF_Pro_Licences_Updates extends Delicious_Brains_API_Licences {

	/**
	 * @var Amazon_S3_And_CloudFront_Pro
	 */
	private $as3cf;

	const MEDIA_USAGE_UNDER       = 1;
	const MEDIA_USAGE_APPROACHING = 2;
	const MEDIA_USAGE_REACHED     = 3;
	const MEDIA_USAGE_EXCEEDED    = 4;

	/**
	 * @param Amazon_S3_And_CloudFront_Pro $as3cf
	 */
	public function __construct( Amazon_S3_And_CloudFront_Pro $as3cf ) {
		$this->as3cf = $as3cf;

		$plugin = new Delicious_Brains_API_Plugin();

		$plugin->global_meta_prefix       = 'aws';
		$plugin->slug                     = 'amazon-s3-and-cloudfront-pro';
		$plugin->name                     = 'WP Offload Media';
		$plugin->version                  = $GLOBALS[ $plugin->global_meta_prefix . '_meta' ][ $plugin->slug ]['version'];
		$plugin->basename                 = $this->as3cf->get_plugin_basename();
		$plugin->dir_path                 = $this->as3cf->get_plugin_dir_path();
		$plugin->prefix                   = 'as3cfpro';
		$plugin->settings_url_path        = $this->as3cf->get_plugin_pagenow() . '?page=amazon-s3-and-cloudfront';
		$plugin->settings_url_hash        = '#/license';
		$plugin->hook_suffix              = $as3cf->hook_suffix;
		$plugin->email_address_name       = 'as3cf';
		$plugin->notices_hook             = 'as3cf_pre_settings_render';
		$plugin->load_hook                = 'as3cf_plugin_load';
		$plugin->expired_licence_is_valid = true;
		$plugin->purchase_url             = $this->as3cf->dbrains_url( '/wp-offload-media/pricing/', array(
			'utm_campaign' => 'WP+Offload+S3',
		) );
		$plugin->licenses_url             = $this->as3cf->dbrains_url( '/my-account/licenses/', array(
			'utm_campaign' => 'WP+Offload+S3',
		) );

		parent::__construct( $plugin );

		$this->init();
	}

	/**
	 * Initialize the actions and filters for the class
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'dashboard_licence_issue_notice' ) );
		add_action( 'network_admin_notices', array( $this, 'dashboard_licence_issue_notice' ) );
		add_filter( 'as3cf_get_notices', array( $this, 'maybe_add_licence_notices' ), 10, 3 );
		add_filter( 'as3cf_get_notices', array( $this, 'maybe_add_update_notices' ), 10, 3 );

		add_filter( 'as3cfpro_js_nonces', array( $this, 'add_licence_nonces' ) );
		add_filter( 'as3cfpro_js_strings', array( $this, 'add_licence_strings' ) );
		add_filter( 'as3cfpro_js_urls', array( $this, 'add_licence_urls' ) );
		add_filter( 'as3cf_addons', array( $this, 'inject_addon_page_links' ) );

		add_action( 'as3cf_plugin_load', array( $this, 'http_dismiss_licence_notice' ) );
		add_action( 'as3cfpro_http_refresh_licence', array( $this, 'do_http_refresh_licence' ) );
		add_action( 'as3cfpro_ajax_check_licence_response', array( $this, 'refresh_licence_notice' ) );
		add_filter( 'as3cfpro_licence_status_message', array( $this, 'licence_status_message' ), 10, 2 );
		add_filter( 'as3cfpro_pre_plugin_row_update_notice', array( $this, 'suppress_plugin_row_update_notices' ), 10, 2 );
		add_action( 'check_admin_referer', array( $this, 'block_updates_with_invalid_licence' ) );
	}

	/**
	 * Accessor for license key
	 *
	 * @return int|mixed|string|WP_Error
	 */
	protected function get_plugin_licence_key() {
		return $this->as3cf->get_setting( 'licence' );
	}

	/**
	 * Setter for license key
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	protected function set_plugin_licence_key( $key ) {
		$this->as3cf->get_settings();
		$this->as3cf->set_setting( 'licence', $key );
		$this->as3cf->save_settings();
	}

	/**
	 * Get licence information that is safe to display.
	 *
	 * @param bool $skip_transient_check
	 *
	 * @return array
	 */
	public function get_licence_info( $skip_transient_check = false ) {
		$result = $this->check( $skip_transient_check );

		$licence_name     = empty( $result['licence_name'] ) ? '' : $result['licence_name'];
		$display_name     = empty( $result['display_name'] ) ? '' : $result['display_name'];
		$user_email       = empty( $result['user_email'] ) ? '' : '&lt;' . $result['user_email'] . '&gt;';
		$email            = empty( $result['user_email'] ) ? array() : array( $result['user_email'] );
		$customer         = trim( $display_name . ' ' . $user_email );
		$support_contacts = empty( $result['support_contacts'] ) ? $email : array_values( array_unique( array_merge( $email, $result['support_contacts'] ) ) );
		$errors           = empty( $result['errors'] ) ? array() : $result['errors'];
		$features         = empty( $result['features'] ) ? array() : $result['features'];

		$customer = empty( $customer ) && ! empty( $email ) ? $email[0] : $customer;

		$plan_usage = _x( 'No License', 'Licence status', 'amazon-s3-and-cloudfront' );
		$limit_info = $this->as3cf->get_total_and_limit_for_licence();

		if ( ! empty( $limit_info ) && isset( $limit_info['total'] ) && isset( $limit_info['limit'] ) ) {
			if ( 0 === $limit_info['limit'] ) {
				$plan_usage = sprintf( _x( '%s / Unlimited', 'Plan usage details', 'amazon-s3-and-cloudfront' ), number_format_i18n( $limit_info['total'] ) );
			} else {
				$plan_usage = sprintf( _x( '%1$s / %2$s media items', 'Plan usage details', 'amazon-s3-and-cloudfront' ), number_format_i18n( $limit_info['total'] ), number_format_i18n( $limit_info['limit'] ) );
			}
		} else {
			$limit_info = array(
				'total'               => -1,
				'limit'               => -1,
				'counts_toward_limit' => true,
			);
		}

		return array(
			'is_defined'              => $this->is_licence_constant(),
			'is_set'                  => (bool) $this->get_licence_key(),
			'is_valid'                => $this->is_valid_licence(),
			'masked_licence'          => $this->get_masked_licence(),
			'status_description'      => $this->licence_status_description(),
			'plan'                    => $licence_name,
			'plan_plus_licence'       => sprintf( _x( '%1$s License', 'Licence description', 'amazon-s3-and-cloudfront' ), $licence_name ),
			'your_active_licence'     => sprintf(
				_x( 'You have an active<span>%1$s</span> license.', 'licence name supplied with leading space if unknown', 'amazon-s3-and-cloudfront' ),
				empty( $licence_name ) ? '' : ' ' . $licence_name
			),
			'customer'                => $customer,
			'support_email_addresses' => $support_contacts,
			'help_message'            => empty( $result['message'] ) ? $this->get_default_help_message() : $result['message'],
			'support_url'             => $this->get_support_url(),
			'errors'                  => $errors,
			'features'                => $features,
			'plan_usage'              => $plan_usage,
			'limit_info'              => $limit_info,
		);
	}

	/**
	 * The URL that support requests are to be posted to for the customer.
	 *
	 * @return string
	 */
	private function get_support_url() {
		return $this->get_url(
			'submit_support_request',
			array(
				'licence_key' => $this->get_licence_key(),
			)
		);
	}

	/**
	 * Get description of licence status.
	 *
	 * @return string
	 */
	public function licence_status_description() {
		$status  = $this->is_licence_expired();
		$strings = $this->add_licence_strings( array() );

		if ( isset( $status['errors'] ) ) {
			reset( $status['errors'] );
			$key = key( $status['errors'] );

			if ( isset( $strings[ $key ] ) ) {
				return $strings[ $key ];
			}

			// Fallback if error type not anticipated.
			return ucwords( str_replace( '_', ' ', $key ) );
		}

		return $strings['valid'];
	}

	/**
	 * Add more nonces to the as3cfpro Javascript object
	 *
	 * @param array $nonces
	 *
	 * @return array
	 */
	function add_licence_nonces( $nonces ) {
		$nonces['check_licence']      = wp_create_nonce( 'check-licence' );
		$nonces['activate_licence']   = wp_create_nonce( 'activate-licence' );
		$nonces['remove_licence']     = wp_create_nonce( 'remove-licence' );
		$nonces['reactivate_licence'] = wp_create_nonce( 'reactivate-licence' );

		return $nonces;
	}

	/**
	 * Add more strings to the as3cfpro Javascript object
	 *
	 * @param array $strings
	 *
	 * @return array
	 */
	public function add_licence_strings( $strings ) {
		$licence_strings = array(
			'licence_title'                  => _x( 'License', 'Page title', 'amazon-s3-and-cloudfront' ),
			'licence_tab_title'              => _x( 'License', 'Tab title', 'amazon-s3-and-cloudfront' ),
			'activate_licence'               => _x( 'Activate License', 'button text', 'amazon-s3-and-cloudfront' ),
			'remove_licence'                 => _x( 'Remove', 'button text', 'amazon-s3-and-cloudfront' ),
			'licence_check_problem'          => __( 'A problem occurred when trying to check the license, please try again.', 'amazon-s3-and-cloudfront' ),
			'enter_licence_key'              => _x( 'Enter License Key...', 'placeholder text', 'amazon-s3-and-cloudfront' ),
			'register_licence_problem'       => __( 'A problem occurred when trying to register the license, please try again.', 'amazon-s3-and-cloudfront' ),
			'licence_registered'             => __( 'Your license has been activated. You will now receive automatic updates and access to email support.', 'amazon-s3-and-cloudfront' ),
			'licence_removed'                => __( 'Licence key removed successfully.', 'amazon-s3-and-cloudfront' ),
			'licence_not_entered'            => __( 'We couldn\'t find your license information.', 'amazon-s3-and-cloudfront' ),
			'licence_checked'                => __( 'License checked', 'amazon-s3-and-cloudfront' ),
			'licence_error'                  => __( 'License error', 'amazon-s3-and-cloudfront' ),
			'please_enter_licence'           => __( 'Please enter a valid license key.', 'amazon-s3-and-cloudfront' ),
			'once_licence_entered'           => __( 'Once entered, you can view your support details.', 'amazon-s3-and-cloudfront' ),
			'fetching_licence'               => __( 'Fetching license details, please wait…', 'amazon-s3-and-cloudfront' ),
			'activate_licence_problem'       => __( 'An error occurred when trying to reactivate your license. Please contact support.', 'amazon-s3-and-cloudfront' ),
			'attempting_to_activate_licence' => __( 'Attempting to activate your license, please wait…', 'amazon-s3-and-cloudfront' ),
			'status'                         => _x( 'Status', 'Current request status', 'amazon-s3-and-cloudfront' ),
			'response'                       => _x( 'Response', 'The message the server responded with', 'amazon-s3-and-cloudfront' ),
			'licence_reactivated'            => __( 'License successfully activated, please wait…', 'amazon-s3-and-cloudfront' ),
			'temporarily_activated_licence'  => __( "<strong>We've temporarily activated your licence and will complete the activation once the Delicious Brains API is available again.</strong><br />Please refresh this page to continue.", 'amazon-s3-and-cloudfront' ),
			'valid'                          => _x( 'Valid', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'no_licence'                     => _x( 'No License', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'licence_not_found'              => _x( 'Not Found', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'subscription_cancelled'         => _x( 'Cancelled', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'no_activations_left'            => _x( 'No Activations Left', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'activation_failed'              => _x( 'Activation Failed', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'subscription_expired'           => _x( 'Expired', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'licence_limit_reached'          => _x( 'The total number of offloaded media items has reached the limit for your license.', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'licence_limit_exceeded'         => _x( 'The total number of offloaded media items has exceeded the limit for your license.', 'Licence status', 'amazon-s3-and-cloudfront' ),
			'plan_usage_title'               => _x( 'Plan usage', 'Section title', 'amazon-s3-and-cloudfront' ),
			'upgrade_plan_cta'               => _x( 'Upgrade plan', 'Upsell call to action', 'amazon-s3-and-cloudfront' ),
			'email_support_title'            => _x( 'Email Support', 'Page title', 'amazon-s3-and-cloudfront' ),
			'select_email'                   => __( 'Select email address...', 'amazon-s3-and-cloudfront' ),
			'email_note'                     => sprintf(
				__( 'Replies will be sent to this email address. Update your name & email in <a href="%1$s" class="link" target="_blank">My Account.</a>', 'amazon-s3-and-cloudfront' ),
				$this->as3cf->get_my_account_url()
			),
			'email_subject_placeholder'      => _x( 'Subject', 'Placeholder', 'amazon-s3-and-cloudfront' ),
			'email_message_placeholder'      => _x( 'Message', 'Placeholder', 'amazon-s3-and-cloudfront' ),
			'attach_diagnostics'             => __( 'Attach the Diagnostic Info (below)', 'amazon-s3-and-cloudfront' ),
			'send_email'                     => _x( 'Send Email', 'Button text', 'amazon-s3-and-cloudfront' ),
			'having_trouble'                 => __( 'Having trouble submitting the form?', 'amazon-s3-and-cloudfront' ),
			'email_instead'                  => __( 'Email your support request to <a href="mailto:as3cf@deliciousbrains.com" target="_blank" class="email link">as3cf@deliciousbrains.com</a>&nbsp;instead.', 'amazon-s3-and-cloudfront' ),
			'send_email_success'             => __( "<strong>Success!</strong> — Thanks for submitting your support request. We'll be in touch soon.", 'amazon-s3-and-cloudfront' ),
			'send_email_post_error'          => _x( "<strong>Error!</strong> — There was a problem submitting your request: ", "Error notice prefix", 'amazon-s3-and-cloudfront' ),
			'send_email_api_error'           => _x( "<strong>Error!</strong> — ", "Error notice prefix", 'amazon-s3-and-cloudfront' ),
			'send_email_unexpected_error'    => _x( "<strong>Error!</strong> — There was an unexpected problem submitting your request, please try again.", "Error notice prefix", 'amazon-s3-and-cloudfront' ),
			'documentation_title'            => _x( 'Documentation', 'Section title', 'amazon-s3-and-cloudfront' ),
		);

		return array_merge( $strings, $licence_strings );
	}

	/**
	 * Add more urls to the as3cfpro Javascript object
	 *
	 * @param array $urls
	 *
	 * @return array
	 */
	public function add_licence_urls( $urls ) {
		$licence_urls = array(
			'purchase' => $this->plugin->purchase_url,
			'licenses' => $this->plugin->licenses_url,
			'account'  => $this->plugin->account_url,
		);

		return array_merge( $urls, $licence_urls );
	}

	/**
	 * Inject the install and download links for available addons
	 * to the AWS Addons page
	 *
	 * @param array $addons
	 *
	 * @return array $addons
	 */
	function inject_addon_page_links( $addons ) {
		if ( ! $this->is_valid_licence( false, false ) ) {
			return $addons;
		}

		foreach ( $addons as $slug => &$addon ) {
			$basename = $this->plugin->get_plugin_basename( $slug );

			if ( ! isset( $this->addons[ $basename ] ) ) {
				continue;
			}

			// Default extra link 'My Account' as Upgrade
			$extra_link = array(
				'url'  => $this->plugin->account_url,
				'text' => __( 'Upgrade', 'amazon-s3-and-cloudfront' ),
			);

			if ( $this->addons[ $basename ]['available'] ) {
				// Addon available to be installed
				$addon['install'] = true;
				// and manually downloaded
				$extra_link['url']  = $this->updates->get_plugin_update_download_url( $slug );
				$extra_link['text'] = __( 'Download', 'amazon-s3-and-cloudfront' );
			}

			$addon['links'][] = $extra_link;
		}

		return $addons;
	}

	/**
	 * Clear the media attachment transients when we refresh the license and find no problems.
	 */
	public function do_http_refresh_licence() {
		// force a check of the license again as we aren't hitting the support tab
		$licence          = $this->get_licence_key();
		$encoded_response = $this->check_licence( $licence );
		$decoded_response = json_decode( $encoded_response, true );

		// When refreshing the license and there are no license errors or license expired,
		// chances are the reason for the "Check Again" is down to license limit issues.
		if ( empty( $decoded_response['errors'] ) || ! empty( $decoded_response['errors']['subscription_expired'] ) ) {
			$this->remove_media_transients();
		}
	}

	/**
	 * Remove media related transients.
	 */
	private function remove_media_transients() {
		// To avoid race conditions, just set the transient to expire very soon if we need to.
		$media_limit_check = get_site_transient( $this->plugin->prefix . '_licence_media_check' );

		if ( $this->counts_toward_limit( $media_limit_check ) ) {
			set_site_transient( $this->plugin->prefix . '_licence_media_check', $media_limit_check, 3 );
		}
	}

	/**
	 * Helper for creating nonced action URLs
	 *
	 * @param string $action
	 * @param bool   $send_to_settings Send back to settings tab
	 * @param bool   $dashboard        Are we displaying elsewhere in the dashboard
	 *
	 * @return string
	 */
	public function get_licence_notice_url( $action, $send_to_settings = true, $dashboard = false ) {
		$action     = $this->plugin->prefix . '-' . $action;
		$query_args = array(
			'nonce' => wp_create_nonce( $action ),
			$action => 1,
		);

		if ( $dashboard ) {
			$query_args['sendback'] = urlencode( $_SERVER['REQUEST_URI'] );
		}

		$path = $this->plugin->settings_url_path;
		if ( $send_to_settings && empty( $query_args['sendback'] ) ) {
			$path .= $this->plugin->settings_url_hash;
		}

		$url = add_query_arg( $query_args, $this->admin_url( $path ) );

		return $url;
	}

	/**
	 * Display our license issue notice which covers -
	 *  - No license
	 *  - Expired licenses
	 *  - Media library larger than license limit
	 *
	 * @param bool $dashboard Are we displaying across the dashboard?
	 * @param bool $skip_transient
	 */
	public function display_licence_issue_notice( $dashboard = false, $skip_transient = false ) {
		$notice = $this->get_licence_issue_notice( $dashboard, $skip_transient );

		if ( ! empty( $notice ) ) {
			$this->render_licence_notice( $notice );
		}
	}

	/**
	 * Get our license issue notice which covers -
	 *  - No license
	 *  - Expired licenses
	 *  - Media library larger than license limit
	 *
	 * @param bool $dashboard Are we displaying across the dashboard?
	 * @param bool $skip_transient
	 *
	 * @return array
	 */
	public function get_licence_issue_notice( $dashboard = false, $skip_transient = false ) {
		// Only check license on primary site of multisite to reduce API calls etc.
		if ( ! is_main_site() ) {
			return array();
		}

		if ( ! $this->as3cf->is_plugin_setup() ) {
			// Don't show the notice if basic plugin requirements are not met.
			return array();
		}

		if ( $dashboard && method_exists( 'AS3CF_Compatibility_Check', 'is_installing_or_updating_plugins' ) && AS3CF_Compatibility_Check::is_installing_or_updating_plugins() ) {
			// Don't show the notice for plugin installs & updates, just too much noise
			return array();
		}

		$licence_check = $this->is_licence_expired();
		$args          = compact( 'dashboard' );

		if ( ! empty( $licence_check['errors']['no_licence'] ) ) {
			return $this->expand_licence_issue_links( $this->get_no_licence_notice( $args ) );
		}

		$media_limit_check   = $this->check_licence_media_limit( $skip_transient );
		$counts_toward_limit = $this->counts_toward_limit( $media_limit_check );

		if ( $counts_toward_limit && isset( $media_limit_check['status']['code'] ) && self::MEDIA_USAGE_REACHED <= $media_limit_check['status']['code'] ) {
			return $this->expand_licence_issue_links( $this->get_over_limit_licence_notice( $args ) );
		} elseif ( $counts_toward_limit && isset( $media_limit_check['status']['code'] ) && self::MEDIA_USAGE_APPROACHING === $media_limit_check['status']['code'] ) {
			return $this->expand_licence_issue_links( $this->get_near_limit_licence_notice( $args ) );
		} elseif ( ! empty( $licence_check['errors']['subscription_expired'] ) ) {
			return $this->expand_licence_issue_links( $this->get_expired_licence_notice( $args ) );
		} elseif ( ! isset( $licence_check['errors'] ) ) {
			$this->clear_licence_issue();
		}

		return array();
	}

	/**
	 * Checks response from check license media limit to see whether site counts toward the limit.
	 *
	 * @param array $media_limit_check
	 *
	 * @return bool
	 */
	public function counts_toward_limit( $media_limit_check ) {
		if (
			isset( $media_limit_check['counts_toward_limit'] ) &&
			! empty( $media_limit_check['counts_toward_limit'] ) &&
			isset( $media_limit_check['limit'] ) &&
			absint( $media_limit_check['limit'] ) > 0
		) {
			return true;
		}

		return false;
	}

	/**
	 * If a notice has link names, expand into usable links.
	 *
	 * @param array $notice
	 *
	 * @return array
	 */
	private function expand_licence_issue_links( $notice ) {
		if ( ! empty( $notice['links'] ) ) {
			$link_map = array(
				'upgrade_now' => sprintf( '<a href="%s" class="as3cf-pro-upgrade-now">%s</a>', $this->as3cf->get_my_account_url(), __( 'Upgrade Your License Now', 'amazon-s3-and-cloudfront' ) ),
				'renew_now'   => sprintf( '<a href="%s" class="as3cf-pro-renew-now">%s</a>', $this->as3cf->get_my_account_url(), __( 'Renew Your License Now', 'amazon-s3-and-cloudfront' ) ),
				'check_again' => sprintf( '<a href="%s" class="as3cf-pro-check-again">%s</a>', $this->get_licence_notice_url( 'check-licence', false, $notice['dashboard'] ), __( 'Check again', 'amazon-s3-and-cloudfront' ) ),
			);

			$notice['links'] = array_map( function ( $link ) use ( $link_map ) {
				return isset( $link_map[ $link ] ) ? $link_map[ $link ] : $link;
			}, $notice['links'] );
		}

		return $notice;
	}

	/**
	 * Get the notice for a missing licence.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function get_no_licence_notice( $args ) {
		if ( $args['dashboard'] ) {
			return array();
		}

		$issue         = 'no_licence';
		$licence_check = $this->is_licence_expired();

		return $this->as3cf->notices->build_notice(
			$licence_check['errors']['no_licence'],
			array_merge( $args, array(
				'custom_id' => 'as3cfpro_licence_notice_' . $issue,
				'heading'   => __( 'Activate Your License', 'amazon-s3-and-cloudfront' ),
				'issue'     => $issue,
				'links'     => array( 'check_again' ),
			) )
		);
	}

	/**
	 * Get the notice for an expired licence.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function get_expired_licence_notice( $args ) {
		$issue = 'subscription_expired';

		if ( $args['dashboard'] ) {
			$heading = sprintf( __( 'Your %s License Has Expired', 'amazon-s3-and-cloudfront' ), $this->plugin->name );
		} else {
			$heading = __( 'Your License Has Expired', 'amazon-s3-and-cloudfront' );
		}

		return $this->as3cf->notices->build_notice(
			__( 'All features will continue to work, but you won\'t have access to software updates or email support.', 'amazon-s3-and-cloudfront' ),
			array_merge( $args, array(
				'custom_id' => 'as3cfpro_licence_notice_' . $issue,
				'heading'   => $heading,
				'issue'     => $issue,
				'links'     => array( 'renew_now', 'check_again' ),
			) )
		);
	}

	/**
	 * Get the notice for a licence approaching its limit.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function get_near_limit_licence_notice( $args ) {
		$media_limit_check = $this->check_licence_media_limit();
		$message           = sprintf(
			__( 'The total number of attachments across the media libraries for your installs (%1$s) is approaching the limit for your license (%2$s).', 'amazon-s3-and-cloudfront' ),
			number_format( absint( $media_limit_check['total'] ) ),
			number_format( absint( $media_limit_check['limit'] ) )
		);

		if ( $args['dashboard'] ) {
			$args['heading'] = sprintf( __( 'Approaching %s License Limit', 'amazon-s3-and-cloudfront' ), $this->plugin->name );
		} else {
			$args['heading'] = __( 'Approaching License Limit', 'amazon-s3-and-cloudfront' );
		}

		$args['issue']     = 'near_limit';
		$args['custom_id'] = 'as3cfpro_licence_notice_' . $args['issue'];
		$args['extra']     = sprintf(
			__( 'When you exceed the limit, all essential features will continue to work, but a few <a href="%1$s">non-essential features</a> will be disabled until you <a href="%2$s">upgrade your license</a> or <a href="%3$s">free-up some of your current limit</a>.', 'amazon-s3-and-cloudfront' ),
			$this->non_essential_features_url(),
			$this->as3cf->get_my_account_url(),
			$this->free_up_limit_url()
		);
		$args['links']     = array( 'upgrade_now', 'check_again' );

		return $this->as3cf->notices->build_notice( $message, $args );
	}

	/**
	 * Get the notice for a licence which has exceeded its limit.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function get_over_limit_licence_notice( $args ) {
		$media_limit_check = $this->check_licence_media_limit();
		$total             = absint( $media_limit_check['total'] );
		$limit             = absint( $media_limit_check['limit'] );

		if ( $args['dashboard'] ) {
			$args['heading'] = sprintf( __( 'Upgrade Your %s License', 'amazon-s3-and-cloudfront' ), $this->plugin->name );
		} else {
			$args['heading'] = __( 'Upgrade Your License', 'amazon-s3-and-cloudfront' );
		}

		$reached  = __( 'The total number of attachments across the media libraries for your installs (%1$s) has reached the limit for your license (%2$s).', 'amazon-s3-and-cloudfront' );
		$exceeded = __( 'The total number of attachments across the media libraries for your installs (%1$s) has exceeded the limit for your license (%2$s).', 'amazon-s3-and-cloudfront' );

		$args['issue']     = 'over_limit';
		$args['custom_id'] = 'as3cfpro_licence_notice_' . $args['issue'];
		$message           = sprintf(
			$total > $limit ? $exceeded : $reached,
			number_format( $total ),
			number_format( $limit )
		);
		$args['extra']     = sprintf(
			__( 'All essential features will continue to work, but a few <a href="%1$s">non-essential features</a> will be disabled until you <a href="%2$s">upgrade your license</a> or <a href="%3$s">free-up some of your current limit</a>.', 'amazon-s3-and-cloudfront' ),
			$this->non_essential_features_url(),
			$this->as3cf->get_my_account_url(),
			$this->free_up_limit_url()
		);
		$args['links']     = array( 'upgrade_now', 'check_again' );

		return $this->as3cf->notices->build_notice( $message, $args );
	}

	/**
	 * Render a licence notice.
	 *
	 * @param array $args
	 */
	public function render_licence_notice( $args = array() ) {
		$args = array_merge( array(
			'heading'     => '',
			'issue'       => '',
			'message'     => '',
			'extra'       => '',
			'links'       => array(),
			'dashboard'   => false,
			'dismissible' => false,
			'dismiss_url' => '',
		), $args );

		// Don't show if current user has dismissed notice
		if ( $args['dashboard'] && get_user_meta( get_current_user_id(), $this->plugin->prefix . '-dismiss-licence-notice' ) ) {
			return;
		}

		if ( $args['dashboard'] ) {
			$args['dismissible'] = true;
			$args['dismiss_url'] = $this->get_licence_notice_url( 'dismiss-licence-notice', false, true );
		}

		$this->as3cf->render_view( 'licence-notice', $args );
		$this->update_licence_issue( $args['issue'] );
	}

	/**
	 * Update the saved license issue type.
	 *
	 * @param string $type
	 */
	protected function update_licence_issue( $type ) {
		if ( $type !== get_site_option( $this->plugin->prefix . '_licence_issue_type' ) ) {
			// Delete the dismissed flag for the user
			delete_user_meta( get_current_user_id(), $this->plugin->prefix . '-dismiss-licence-notice' );

			// Store the type of issue for comparison later
			update_site_option( $this->plugin->prefix . '_licence_issue_type', $type );
		}
	}

	/**
	 * Clear the saved licence issue type.
	 */
	protected function clear_licence_issue() {
		delete_site_option( $this->plugin->prefix . '_licence_issue_type' );
	}

	/**
	 * Get the document href for non-essential features.
	 *
	 * @return string
	 */
	public function non_essential_features_url() {
		return $this->as3cf->dbrains_url( '/wp-offload-media/doc/non-essential-features/', array(
			'utm_campaign' => 'error+messages',
		) );
	}

	/**
	 * Get the document href for details about freeing up licence limit.
	 *
	 * @return string
	 */
	public function free_up_limit_url() {
		return $this->as3cf->dbrains_url( '/wp-offload-media/pricing/', array(
			'utm_campaign' => 'error+messages',
		), 'free-up-limit' );
	}

	/**
	 * Dismiss the license issue notice
	 */
	function http_dismiss_licence_notice() {
		if ( isset( $_GET[ $this->plugin->prefix . '-dismiss-licence-notice' ] ) && wp_verify_nonce( $_GET['nonce'], $this->plugin->prefix . '-dismiss-licence-notice' ) ) { // input var okay
			$hash = ( isset( $_GET['hash'] ) ) ? '#' . sanitize_title( $_GET['hash'] ) : ''; // input var okay

			// Store the dismissed flag against the user
			update_user_meta( get_current_user_id(), $this->plugin->prefix . '-dismiss-licence-notice', true );

			$sendback = filter_input( INPUT_GET, 'sendback' );
			$sendback = $sendback ? $sendback : $this->admin_url( $this->plugin->settings_url_path . $hash );

			// redirecting because we don't want to keep the query string in the web browsers address bar
			wp_safe_redirect( $sendback );
			exit;
		}
	}

	/**
	 * Display the license issue notice site wide except on our plugin page
	 */
	public function dashboard_licence_issue_notice() {
		if ( isset( $_GET['page'] ) && 'amazon-s3-and-cloudfront' === $_GET['page'] ) {
			return;
		}

		global $as3cf_compat_check;
		if ( ! $as3cf_compat_check->check_capabilities() ) {
			return;
		}

		$this->display_licence_issue_notice( true );
	}

	/**
	 * Check the license is not over its limit for media library items
	 *
	 * @param bool $skip_transient Whether to force database query and skip transient, default false
	 * @param bool $force          Whether to force database query and skip static cache, implies $skip_transient, default false
	 *
	 * @return bool|array
	 */
	public function check_licence_media_limit( $skip_transient = false, $force = false ) {
		$media_limit_check = get_site_transient( $this->plugin->prefix . '_licence_media_check' );

		if ( ! $force && ! $this->is_valid_licence() ) {
			return $media_limit_check;
		}

		if (
			! $force && ! empty( $media_limit_check ) &&
			(
				! $this->counts_toward_limit( $media_limit_check ) ||
				( isset( $media_limit_check['status']['code'] ) && self::MEDIA_USAGE_APPROACHING > $media_limit_check['status']['code'] )
			)
		) {
			// We're in no rush.
			$skip_transient = false;
		}

		if ( $skip_transient || false === $media_limit_check || isset( $media_limit_check['errors'] ) ) {
			$media_counts = $this->as3cf->media_counts( $skip_transient, $force );

			// We may have needed to force a count, but without a license we can't call the API.
			if ( ! ( $licence_key = $this->get_licence_key() ) ) {
				return false;
			}

			$args = array(
				'licence_key'   => $licence_key,
				'site_url'      => $this->home_url,
				'library_total' => $media_counts['offloaded'],
			);

			$provider = $this->as3cf->get_storage_provider();

			if ( ! empty( $provider ) && ! empty( $provider->get_provider_key_name() ) ) {
				$args['provider'] = $provider->get_provider_key_name();
			}

			$response = $this->api_request( 'check_licence_media_limit', $args );

			$media_limit_check = json_decode( $response, true );

			// Can't decode json so assume ok, but don't cache response
			if ( ! $media_limit_check ) {
				return array();
			}

			set_site_transient( $this->plugin->prefix . '_licence_media_check', $media_limit_check );
		}

		return $media_limit_check;
	}

	/**
	 * Check if the license is under the media limit
	 *
	 * @param array $media_limit_response Optional pre-fetched media limit response data.
	 *
	 * @return bool
	 */
	public function is_licence_over_media_limit( $media_limit_response = array() ) {
		if ( ! empty( $media_limit_response ) && is_array( $media_limit_response ) ) {
			$media_limit_check = $media_limit_response;
		} else {
			$media_limit_check = $this->check_licence_media_limit();
		}

		if ( ! $this->counts_toward_limit( $media_limit_check ) ) {
			return false;
		}

		if ( ! isset( $media_limit_check['status'] ) ) {
			return false;
		}

		if ( $media_limit_check['status']['code'] < self::MEDIA_USAGE_REACHED ) {
			return false;
		}

		return true;
	}

	/**
	 * Return the custom license error to the API when activating / checking a license
	 *
	 * @param array $decoded_response
	 *
	 * @return array
	 */
	public function refresh_licence_notice( $decoded_response ) {
		ob_start();
		$this->display_licence_issue_notice( false, true );
		$licence_error = ob_get_contents();
		ob_end_clean();

		if ( $licence_error ) {
			$license                       = $this->is_licence_expired();
			$decoded_response['errors']    = empty( $license['errors'] ) ? '' : $license['errors'];
			$decoded_response['pro_error'] = $licence_error;
		}

		return $decoded_response;
	}

	/**
	 * Override the default license expired message for the email support section
	 *
	 * @param string $message
	 * @param array  $errors
	 *
	 * @return string
	 */
	public function licence_status_message( $message, $errors ) {
		if ( isset( $errors['subscription_expired'] ) ) {
			$check_licence_again_url = $this->admin_url( $this->plugin->settings_url_path . '&nonce=' . wp_create_nonce( $this->plugin->prefix . '-check-licence' ) . '&' . $this->plugin->prefix . '-check-licence=1' );

			$url     = $this->as3cf->dbrains_url( '/my-account/', array(
				'utm_campaign' => 'error+messages',
			) );
			$message = sprintf( __( '<strong>Your License Has Expired</strong> &mdash; Please visit <a href="%s" target="_blank">My Account</a> to renew your license and continue receiving access to email support.' ), $url ) . ' ';
			$message .= sprintf( '<a href="%s">%s</a>', $check_licence_again_url, __( 'Check again' ) );
		}

		return $message;
	}

	/**
	 * Don't show plugin row update notices when AWS not set up
	 *
	 * @param bool  $pre
	 * @param array $licence_response
	 *
	 * @return bool
	 */
	function suppress_plugin_row_update_notices( $pre, $licence_response ) {
		global $amazon_web_services;

		if ( isset( $licence_response['errors']['no_licence'] ) && ! $amazon_web_services->are_access_keys_set() ) {
			// Don't show the activate license notice if we haven't set up AWS keys
			return true;
		}

		return $pre;
	}

	/**
	 * Throw a nonce error if trying to update the plugin or addons
	 * with a missing or invalid license
	 *
	 * @param string     $action
	 * @param bool|false $result
	 *
	 * @return bool
	 */
	function block_updates_with_invalid_licence( $action, $result = false ) {
		if ( 'bulk-update-plugins' !== $action ) {
			return $result;
		}

		if ( ! isset( $_GET['plugins'] ) && ! isset( $_POST['checked'] ) ) {
			return $result;
		}

		if ( isset( $_GET['plugins'] ) ) {
			$plugins = explode( ',', stripslashes( $_GET['plugins'] ) );
		} elseif ( isset( $_POST['checked'] ) ) {
			$plugins = (array) $_POST['checked'];
		} else {
			// No plugins selected at all, move on
			return $result;
		}

		$plugins          = array_map( 'urldecode', $plugins );
		$our_plugins      = array_keys( $this->addons );
		$our_plugins[]    = $this->plugin->basename;
		$matching_plugins = array_intersect( $plugins, $our_plugins );

		if ( empty( $matching_plugins ) ) {
			// None of our addons or plugin are being updated
			return $result;
		}

		$licence_check = $this->is_licence_expired( true );

		foreach ( $plugins as $plugin ) {
			if ( in_array( $plugin, $our_plugins ) ) {
				$plugin_name   = $this->plugin->name;
				$parent_plugin = '';
				if ( isset( $this->addons[ $plugin ] ) ) {
					$plugin_name   = $this->addons[ $plugin ]['name'];
					$parent_plugin = ' ' . sprintf( __( 'for %s', 'amazon-s3-and-cloudfront' ), $this->plugin->name );
				}

				if ( isset( $licence_check['errors']['no_licence'] ) ) {
					$html = sprintf( __( '<strong>Activate Your License</strong> &mdash; You can only update %1$s with a valid license key%2$s.', 'amazon-s3-and-cloudfront' ), $plugin_name, $parent_plugin );
					$html .= '</p><p><a target="_parent" href="' . $this->admin_url( $this->plugin->settings_url_path ) . $this->plugin->settings_url_hash . '">' . _x( 'Activate', 'Activate license', 'amazon-s3-and-cloudfront' ) . '</a> | ';
					$html .= '<a target="_parent" href="' . $this->plugin->purchase_url . '">' . _x( 'Purchase', 'Purchase license', 'amazon-s3-and-cloudfront' ) . '</a>';
				} elseif ( isset( $licence_check['errors']['subscription_expired'] ) ) {
					$html = sprintf( __( '<strong>Your License Has Expired</strong> &mdash; You can only update %1$s with a valid license key%2$s. Please visit <a href="%3$s" target="_parent">My Account</a> to renew your license and continue receiving plugin updates.', 'amazon-s3-and-cloudfront' ), $plugin_name, $parent_plugin, $this->plugin->account_url );
				} else {
					// License valid, move along
					return $result;
				}

				if ( isset( $_GET['plugins'] ) ) {
					$clean_plugin = addslashes( urlencode( $plugin ) );

					// Check for assortment of versions of plugin, with leading commas
					$needles = array(
						',' . $plugin,
						',' . $clean_plugin,
						$plugin,
						$clean_plugin,
					);

					// Remove plugin from the global var
					$_GET['plugins'] = str_replace( $needles, '', $_GET['plugins'] );

					if ( '' === $_GET['plugins'] ) {
						// No plugins, remove the var
						unset( $_GET['plugins'] );
					}
				} elseif ( isset( $_POST['checked'] ) ) {
					foreach ( $_POST['checked'] as $key => $checked_plugin ) {
						if ( in_array( $checked_plugin, array( $plugin, urlencode( $plugin ) ) ) ) {
							// Remove plugin from the global var
							unset( $_POST['checked'][ $key ] );
						}

						if ( empty( $_POST['checked'] ) ) {
							// No plugins, remove the var
							unset( $_POST['checked'] );
						}
					}
				}

				// Display license error notice
				$this->as3cf->render_view( 'error-fatal', array( 'message' => $html ) );
			}
		}

		return $result;
	}

	/**
	 * Error log method
	 *
	 * @param mixed $error
	 * @param bool  $additional_error_var
	 */
	function log_error( $error, $additional_error_var = false ) {
		AS3CF_Error::log( $error, 'PRO' );

		if ( false !== $additional_error_var ) {
			AS3CF_Error::log( $additional_error_var, 'PRO' );
		}
	}

	/**
	 * Maybe add a licence notice to the notices being displayed on settings page.
	 *
	 * @handles as3cf_notices
	 *
	 * @param array       $notices
	 * @param string|bool $tab      Only interested in notices for a particular tab?
	 * @param bool        $all_tabs Only interested in notices that are not restricted to any tab?
	 *
	 * @return array
	 */
	public function maybe_add_licence_notices( array $notices, $tab, bool $all_tabs ): array {
		// Only add licence notice when filter fires for cross tab notices, e.g. REST-API.
		if ( ! empty( $tab ) || $all_tabs !== true ) {
			return $notices;
		}

		$notice = $this->get_licence_issue_notice();

		if ( ! empty( $notice ) ) {
			$notice['dismissible'] = false;
			$notices[]             = $notice;
		}

		return $notices;
	}

	/**
	 * Maybe add an update notice to the notices being displayed on settings page.
	 *
	 * @handles as3cf_notices
	 *
	 * @param array       $notices
	 * @param string|bool $tab      Only interested in notices for a particular tab?
	 * @param bool        $all_tabs Only interested in notices that are not restricted to any tab?
	 *
	 * @return array
	 */
	public function maybe_add_update_notices( array $notices, $tab, bool $all_tabs ): array {
		// Only add update notice when filter fires for cross tab notices, e.g. REST-API.
		if ( ! empty( $tab ) || $all_tabs !== true ) {
			return $notices;
		}

		// Only check for updates on primary site of multisite to reduce API calls etc.
		if ( ! is_main_site() ) {
			return array();
		}

		$details = $this->updates->get_plugin_update_notices();

		if ( ! empty( $details ) ) {
			foreach ( $details as $detail ) {
				$args = array(
					'type'                  => 'warning',
					'dismissible'           => false,
					'only_show_in_settings' => true,
					'custom_id'             => 'as3cfpro_update_notice_' . $detail['slug'],
					'heading'               => $detail['heading'],
				);

				$notice    = $this->as3cf->notices->build_notice( $detail['message'], $args );
				$notices[] = $notice;
			}
		}

		return $notices;
	}
}
