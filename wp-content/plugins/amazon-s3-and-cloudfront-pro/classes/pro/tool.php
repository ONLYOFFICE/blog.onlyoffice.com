<?php

namespace DeliciousBrains\WP_Offload_Media\Pro;

use Amazon_S3_And_CloudFront_Pro;
use AS3CF_Pro_Utils;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;

abstract class Tool {

	/**
	 * @var Amazon_S3_And_CloudFront_Pro
	 */
	protected $as3cf;

	/**
	 * @var string
	 */
	protected $prefix = 'as3cf';

	/**
	 * @var string
	 */
	protected $tab = 'tools';

	/**
	 * @var string
	 */
	protected $type = 'tool';

	/**
	 * @var string
	 */
	protected $view = 'tool';

	/**
	 * @var int
	 */
	protected $priority = 0;

	/**
	 * @var string
	 */
	protected $tool_key;

	/**
	 * @var string
	 */
	protected $tool_slug;

	/**
	 * @var string
	 */
	protected $errors_key_prefix;

	/**
	 * @var string
	 */
	protected $errors_key;

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array();

	/**
	 * @var bool
	 */
	protected static $requires_bucket_access = true;

	/**
	 * AS3CF_Tool constructor.
	 *
	 * @param Amazon_S3_And_CloudFront_Pro $as3cf
	 */
	public function __construct( $as3cf ) {
		$this->as3cf             = $as3cf;
		$this->tool_slug         = str_replace( array( ' ', '_' ), '-', $this->tool_key );
		$this->errors_key_prefix = 'as3cf_tool_errors_';
		$this->errors_key        = $this->errors_key_prefix . $this->tool_key;
	}

	/**
	 * Initialize the tool.
	 */
	public function init() {
		add_filter( 'as3cfpro_js_strings', array( $this, 'add_js_strings' ) );
		add_filter( 'as3cfpro_js_settings', array( $this, 'add_js_settings' ) );

		// Notices.
		add_filter( 'as3cf_get_notices', array( $this, 'maybe_add_tool_errors_to_notice' ), 10, 3 );
	}

	/**
	 * Add strings for the Tools to the Javascript
	 *
	 * @param array $strings
	 *
	 * @return array
	 *
	 * Note: To be overridden by a tool if required.
	 */
	public function add_js_strings( $strings ) {
		return $strings;
	}

	/**
	 * Add settings for the Tools to the Javascript
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function add_js_settings( $settings ) {
		return $settings;
	}

	/**
	 * Priority
	 *
	 * @param int $priority
	 *
	 * @return $this
	 */
	public function priority( $priority ) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get tools key.
	 *
	 * @return string
	 */
	public function get_tool_key() {
		return $this->tool_key;
	}

	/**
	 * Get tab.
	 *
	 * @return string
	 */
	public function get_tab() {
		return $this->tab;
	}

	/**
	 * Get info for tool, including current status.
	 *
	 * @return array
	 */
	public function get_info() {
		return array(
			'id'                     => $this->tool_key,
			'tab'                    => $this->tab,
			'priority'               => $this->priority,
			'slug'                   => $this->tool_slug,
			'type'                   => $this->type,
			'render'                 => $this->should_render(),
			'is_processing'          => $this->is_processing(),
			'requires_bucket_access' => $this->requires_bucket_access(),
		);
	}

	/**
	 * Should we render the tool's UI?
	 *
	 * @return bool
	 */
	public function should_render() {
		return true;
	}

	/**
	 * Are we currently processing?
	 *
	 * @return bool
	 */
	protected function is_processing() {
		return false;
	}

	/**
	 * Is queued?
	 *
	 * @return bool
	 */
	public function is_queued(): bool {
		return false;
	}

	/**
	 * Is the tool currently active, e.g. starting, working, paused or finishing up?
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return false;
	}

	/**
	 * Get the errors created by the tool
	 *
	 * @param array $default
	 *
	 * @return array
	 */
	public function get_errors( $default = array() ) {
		return get_site_option( $this->errors_key, $default );
	}

	/**
	 * Update the saved errors for the tool
	 *
	 * @param array $errors
	 */
	public function update_errors( $errors ) {
		update_site_option( $this->errors_key, $errors );
	}

	/**
	 * Clear all errors created by the tool
	 */
	protected function clear_errors() {
		delete_site_option( $this->errors_key );
	}

	/**
	 * Update the error notice
	 *
	 * @param array $errors
	 */
	public function update_error_notice( $errors = array() ) {
		if ( empty( $errors ) ) {
			$errors = $this->get_errors();
		}

		if ( ! empty( $errors ) ) {
			$args = array(
				'type'              => 'error',
				'class'             => 'tool-error',
				'flash'             => false,
				'only_show_to_user' => false,
				'only_show_on_tab'  => $this->tab,
				'custom_id'         => $this->errors_key,
				'user_capabilities' => array( 'as3cfpro', 'is_plugin_setup' ),
			);

			// Try and re-use some of existing notice to avoid churn in db or front end.
			$existing_notice = $this->as3cf->notices->find_notice_by_id( $this->errors_key );

			if ( ! empty( $existing_notice ) ) {
				$args = array_merge( $existing_notice, $args );
			}

			$message = $this->get_error_notice_message();

			$this->as3cf->notices->add_notice( $message, $args );
		} else {
			$this->as3cf->notices->remove_notice_by_id( $this->errors_key );
		}
	}

	/**
	 * Undismiss error notice for all users.
	 */
	public function undismiss_error_notice() {
		$this->as3cf->notices->undismiss_notice_for_all( $this->errors_key );
	}

	/**
	 * Dismiss one or all errors for a source item.
	 *
	 * @param int        $blog_id
	 * @param string     $source_type
	 * @param int        $source_id
	 * @param string|int $errors Optional indicator of which error to dismiss for source item, default 'all'.
	 */
	public function dismiss_errors( $blog_id, $source_type, $source_id, $errors = 'all' ) {
		$saved_errors = $this->get_errors();

		foreach ( $saved_errors as $idx => &$saved_error ) {
			if ( $saved_error->blog_id !== $blog_id ) {
				continue;
			}

			if ( $saved_error->source_type !== $source_type || $saved_error->source_id != $source_id ) {
				continue;
			}

			// Remove all errors for this source item?
			if ( $errors === 'all' ) {
				unset( $saved_errors[ $idx ] );
				break;
			}

			// If the saved error message for this item is an array, remove just the one index
			if ( isset( $saved_error->messages[ $errors ] ) ) {
				// Break the object reference. See GitHub issue #2635
				$saved_error = clone $saved_error;
				unset( $saved_error->messages[ $errors ] );

				// If the array is now empty, remove the entire error item
				if ( empty( $saved_error->messages ) ) {
					unset( $saved_errors[ $idx ] );
				} else {
					// Force a reindex of the array to avoid issues with JSON encoding switching to Object if there's non-sequential numeric keys.
					$saved_error->messages = array_values( $saved_error->messages );
				}
			}

			// Whether we dismissed anything or not, we found and processed the expected source item's errors.
			break;
		}

		$updated = AS3CF_Pro_Utils::array_prune_recursive( $saved_errors );
		$this->update_errors( $updated );
		$this->update_error_notice();
	}

	/**
	 * Maybe add error details to this tool's error notice.
	 *
	 * @param array  $notices  An array of notices.
	 * @param string $tab      Optionally restrict to notifications for a specific tab.
	 * @param bool   $all_tabs Optionally return all tab specific notices regardless of tab.
	 *
	 * @return array
	 */
	public function maybe_add_tool_errors_to_notice( array $notices, $tab = '', $all_tabs = false ) {
		if ( ! empty( $notices ) ) {
			$errors = $this->get_errors();

			if ( empty( $errors ) ) {
				return $notices;
			}

			foreach ( $notices as $idx => $notice ) {
				if (
					! empty( $notice['class'] ) &&
					'tool-error' === $notice['class'] &&
					! empty( $notice['id'] ) &&
					$notice['id'] === $this->errors_key
				) {
					$details = array();
					foreach ( $errors as $error ) {
						// If the error is stored as an array, it's almost certainly stored
						// in a previous format/structure that we can't render properly.
						// This will be corrected by the upgrade process, but that process may
						// not have completed yet.
						if ( is_array( $error ) ) {
							continue;
						}

						/** @var Item $class */
						$class = $this->as3cf->get_source_type_class( $error->source_type );

						$this->as3cf->switch_to_blog( $error->blog_id );

						$details[] = array(
							'blog_id'          => $error->blog_id,
							'source_type'      => $error->source_type,
							'source_type_name' => $this->as3cf->get_source_type_name( $error->source_type ),
							'source_id'        => $error->source_id,
							'edit_url'         => $class::admin_link( $error ),
							'messages'         => $error->messages,
						);

						$this->as3cf->restore_current_blog();
					}
					$notices[ $idx ]['errors'] = array(
						'tool_key' => $this->tool_key,
						'details'  => $details,
					);
					break;
				}
			}
		}

		return $notices;
	}

	/**
	 * Tool specific message for error notice.
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		return '';
	}

	/**
	 * Get the constant used to define whether tool should always be shown (implemented as required by subclass).
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function show_tool_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$show_tool_constants );
	}

	/**
	 * Count media files in bucket.
	 *
	 * @return int
	 */
	protected function count_offloaded_media_files() {
		static $count;

		if ( is_null( $count ) ) {
			$media_counts = $this->as3cf->media_counts();
			$count        = $media_counts['offloaded'];
		}

		return $count;
	}

	/**
	 * Does the tool need authenticated access to the bucket?
	 *
	 * @return bool
	 */
	public static function requires_bucket_access(): bool {
		return static::$requires_bucket_access;
	}
}
