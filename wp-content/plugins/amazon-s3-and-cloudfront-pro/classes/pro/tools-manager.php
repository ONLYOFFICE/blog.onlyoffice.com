<?php

namespace DeliciousBrains\WP_Offload_Media\Pro;

use Amazon_S3_And_CloudFront_Pro;
use DeliciousBrains\WP_Offload_Media\API\V1\State;

class Tools_Manager {

	/**
	 * @var Tools_Manager
	 */
	protected static $instance;

	/**
	 * Registered tools.
	 *
	 * @var array
	 */
	private $tools = array();

	/**
	 * Make this class a singleton.
	 *
	 * Use this instead of __construct().
	 *
	 * @param Amazon_S3_And_CloudFront_Pro $as3cf
	 *
	 * @return Tools_Manager
	 */
	public static function get_instance( $as3cf ) {
		if ( ! isset( static::$instance ) && ! ( self::$instance instanceof Tools_Manager ) ) {
			static::$instance = new Tools_Manager();
			// Initialize the class
			static::$instance->init( $as3cf );
		}

		return static::$instance;
	}

	/**
	 * Init.
	 *
	 * @param Amazon_S3_And_CloudFront_Pro $as3cf
	 */
	private function init( $as3cf ) {
		add_filter( 'as3cfpro_js_strings', array( $this, 'add_strings' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) ); // phpcs:ignore WordPress.WP.CronInterval
		add_filter( $as3cf->get_plugin_prefix() . '_api_response_get_' . State::name(), array( $this, 'api_response' ) );
	}

	/**
	 * Add additional translated strings for tools.
	 *
	 * @param array $strings
	 *
	 * @return array
	 */
	public function add_strings( $strings ) {
		return array_merge( $strings, array(
			'pause_button'                => _x( 'Pause', 'Button text', 'amazon-s3-and-cloudfront' ),
			'resume_button'               => _x( 'Resume', 'Button text', 'amazon-s3-and-cloudfront' ),
			'disabled_tool_button'        => _x( 'Disabled because another background process is running.', 'Disabled button tooltip', 'amazon-s3-and-cloudfront' ),
			'disabled_tool_bucket_access' => _x( 'Disabled because this tool requires write access to the bucket.', 'Disabled button tooltip', 'amazon-s3-and-cloudfront' ),
			'item'                        => __( 'Item', 'amazon-s3-and-cloudfront' ),
			'media_library_item'          => _x( 'Media Library Item', 'Source type for item error', 'amazon-s3-and-cloudfront' ),
			'edit_item'                   => _x( 'Edit', 'Link title', 'amazon-s3-and-cloudfront' ),
			'dismiss_all'                 => _x( 'Dismiss All', 'Link title', 'amazon-s3-and-cloudfront' ),
			'dismiss'                     => _x( 'Dismiss', 'Link title', 'amazon-s3-and-cloudfront' ),

			// No tools to show yet
			'no_tools_header'             => _x( 'No Tools Available (Yet)', 'No tools graphic', 'amazon-s3-and-cloudfront' ),
			'no_tools_description'        => _x( 'Once an active license and media items are detected, the following tools will become available for bulk management between this server and the storage provider.', 'No tools graphic', 'amazon-s3-and-cloudfront' ),
		) );
	}

	/**
	 * Add cron schedules.
	 *
	 * @param array $schedules
	 *
	 * @return mixed
	 */
	public function cron_schedules( $schedules ) {
		if ( property_exists( $this, 'cron_interval' ) ) {
			$interval = apply_filters( 'as3cf_tool_cron_interval', $this->cron_interval );
		} else {
			$interval = apply_filters( 'as3cf_tool_cron_interval', 1 );
		}

		if ( 1 === $interval ) {
			$display = __( 'Every Minute', 'amazon-s3-and-cloudfront' );
		} else {
			$display = sprintf( __( 'Every %d Minutes', 'amazon-s3-and-cloudfront' ), $interval );
		}

		// Adds our schedule to the existing schedules.
		$schedules['as3cf_tool_cron_interval'] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			'display'  => $display,
		);

		return $schedules;
	}

	/**
	 * If a get state API response is an array without any info for tools, add tools info.
	 *
	 * @handles as3cf_api_response_get_state
	 *
	 * @param mixed $response
	 *
	 * @return mixed
	 */
	public function api_response( $response ) {
		if ( empty( $response ) || is_wp_error( $response ) || ! is_array( $response ) || isset( $response['tools'] ) ) {
			return $response;
		}

		$response['tools'] = $this->get_tools_info();

		return $response;
	}

	/**
	 * Register a tool.
	 *
	 * @param Tool   $tool
	 * @param string $context
	 *
	 * @return bool
	 */
	public function register_tool( Tool $tool, $context = 'background' ) {
		if ( ! empty( $this->tools[ $context ][ $tool->get_tool_key() ] ) ) {
			return false;
		}

		$this->tools[ $context ][ $tool->get_tool_key() ] = $tool;

		$tool->priority( $this->get_tools_count() )->init();

		return true;
	}

	/**
	 * Get tool.
	 *
	 * @param string $name
	 *
	 * @return bool|Tool
	 */
	public function get_tool( $name ) {
		foreach ( $this->tools as $context ) {
			if ( array_key_exists( $name, $context ) ) {
				return $context[ $name ];
			}
		}

		return false;
	}

	/**
	 * Get tools count.
	 *
	 * @return int
	 */
	public function get_tools_count() {
		$count = 0;

		foreach ( $this->tools as $context ) {
			$count += count( $context );
		}

		return $count;
	}

	/**
	 * Get all tools.
	 *
	 * @param string $context Optional context to restrict by.
	 *
	 * @return array
	 */
	public function get_tools( $context = null ) {
		$result = array();

		foreach ( $this->tools as $_context => $tools ) {
			if ( ! empty( $context ) && $_context !== $context ) {
				continue;
			}
			/**
			 * @var string $key
			 * @var Tool   $tool
			 */
			foreach ( $tools as $key => $tool ) {
				$result[ $key ] = $tool;
			}
		}

		return $result;
	}

	/**
	 * Get tool's info, including current status.
	 *
	 * @param string $context Optional context to restrict by.
	 *
	 * @return array
	 */
	public function get_tools_info( $context = null ) {
		$data = array();

		/** @var Tool $tool */
		foreach ( $this->get_tools( $context ) as $key => $tool ) {
			$data[ $key ] = $tool->get_info();
		}

		return $data;
	}

	/**
	 * Get running tool, or null if none running.
	 *
	 * @param string $context Optional context to restrict by.
	 *
	 * @return Tool|null
	 */
	public function get_running_tool( $context = null ) {
		/** @var Tool $tool */
		foreach ( $this->get_tools( $context ) as $tool ) {
			$data = $tool->get_info();

			if ( ! empty( $data['is_processing'] ) || ! empty( $data['is_queued'] ) || ! empty( $data['is_paused'] ) ) {
				return $tool;
			}
		}

		return null;
	}

	/**
	 * Try and perform the requested action for a tool identified by its key.
	 *
	 * @param string $tool_key
	 * @param string $action
	 *
	 * @return bool
	 */
	public function perform_action( $tool_key, $action ) {
		$tool = $this->get_tool( $tool_key );

		if ( false === $tool ) {
			return false;
		}

		if ( ! method_exists( $tool, 'handle_' . $action ) ) {
			return false;
		}

		$running_tool = $this->get_running_tool();

		// Only one tool can be running or interacted with at once.
		if ( ! empty( $running_tool ) && $tool->get_tool_key() !== $running_tool->get_tool_key() ) {
			return false;
		}

		call_user_func( array( $tool, 'handle_' . $action ) );

		return true;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside this class.
	 */
	protected function __construct() {
	}

	/**
	 * As this class is a singleton it should not be clone-able.
	 */
	protected function __clone() {
	}

	/**
	 * As this class is a singleton it should not be able to be un-serialized.
	 */
	public function __wakeup() {
	}
}
