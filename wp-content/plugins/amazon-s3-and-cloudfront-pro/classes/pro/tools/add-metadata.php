<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Add_Metadata_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Add_Metadata extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'add_metadata';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_ADD_METADATA_TOOL',
	);

	/**
	 * @var bool
	 */
	protected static $requires_bucket_access = false;

	/**
	 * Limit the item types that this tool handles.
	 *
	 * @var array
	 */
	protected $source_types = array(
		'media-library',
	);

	/**
	 * Get a list of key names for tools that are related to the current tool.
	 *
	 * @return array
	 */
	public function get_related_tools() {
		$last_started = get_site_option( $this->prefix . '_' . $this->get_tool_key() . '_last_started' );

		if ( empty( $last_started ) ) {
			return array();
		}

		return array(
			'reverse_add_metadata',
			'verify_add_metadata',
		);
	}

	/**
	 * Handle start.
	 */
	public function handle_start() {
		update_site_option( $this->prefix . '_' . $this->get_tool_key() . '_last_started', time() );

		parent::handle_start();
	}

	/**
	 * Should render.
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( ! $this->as3cf->is_pro_plugin_setup() ) {
			return false;
		}

		if ( false !== static::show_tool_constant() && constant( static::show_tool_constant() ) ) {
			return true;
		}

		return $this->is_active();
	}

	/**
	 * Get the tool's name. Defaults to its title text.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Add Metadata', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Add metadata for all media not offloaded', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return htmlspecialchars( __( "If you already have your site's media in a bucket in the cloud, you can configure WP Offload Media for this bucket and storage paths, then run this tool to go through all your media that hasn't been offloaded yet and add the metadata WP Offload Media needs to manage your media in that bucket and rewrite URLs. After this tool runs, it will give you an undo option (remove all metadata added by this tool) and an option to go through all the media items that we added metadata for, check that the files exist in the bucket, and remove any new metadata where files are missing.", 'amazon-s3-and-cloudfront' ), ENT_QUOTES );
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Add Metadata', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Adding metadata to Media Library', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Adding metadata…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Add Metadata Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at adding metadata to your media library have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%s</strong> &mdash; %s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Add_Metadata_Process( $this->as3cf, $this );
	}
}
