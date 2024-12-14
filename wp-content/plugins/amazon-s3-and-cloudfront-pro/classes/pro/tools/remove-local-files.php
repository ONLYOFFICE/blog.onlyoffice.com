<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Remove_Local_Files_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Remove_Local_Files extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'remove_local_files';

	/**
	 * @var bool
	 */
	protected static $requires_bucket_access = false;

	/**
	 * Should render.
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( ! $this->as3cf->is_pro_plugin_setup() ) {
			return false;
		}

		return $this->as3cf->get_setting( 'remove-local-file', false ) && $this->count_offloaded_media_files();
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Remove all files from server', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'You can use this tool to delete all media files from your local server that have already been offloaded.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = __( 'You\'ve enabled the "Remove Local Media" option. Do you want to remove all existing files from the server that have already been offloaded?', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::settings_more_info_link(
			'remove-local-file',
			'',
			'remove+local+files'
		);

		return $mesg;
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Begin Removal', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Removing media files from your local server.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Removing local…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Message for error notice
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Remove From Local Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at removing your offloaded media library files from the server have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%s</strong> &mdash; %s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Remove_Local_Files_Process( $this->as3cf, $this );
	}
}
