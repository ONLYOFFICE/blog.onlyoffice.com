<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Copy_Buckets_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Copy_Buckets extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'copy_buckets';

	/**
	 * @var string
	 */
	protected $tab = 'media';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_COPY_BUCKETS_TOOL',
		'WPOS3_SHOW_COPY_BUCKETS_TOOL',
	);

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
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Copy Files', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Copy files to new bucket', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Begin Copy', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Copying media items between buckets.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Copying…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'Would you like to consolidate your offloaded media files by copying them into the currently selected bucket? All existing offloaded media URLs will be updated to reference the new bucket.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = __( 'Would you like to copy media files from their current bucket to this new bucket?', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::more_info_link(
			'/wp-offload-media/doc/how-to-copy-media-between-buckets/',
			'copy+buckets',
			'change'
		);

		return $mesg;
	}

	/**
	 * @inheritDoc
	 */
	public function get_doc_url() {
		global $as3cf;

		$args = array( 'utm_campaign' => 'copy+buckets' );

		return $as3cf::dbrains_url( '/wp-offload-media/doc/how-to-copy-media-between-buckets/', $args, 'change' );
	}

	/**
	 * Message for error notice.
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Copy Bucket Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at copying your media library between buckets have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%s</strong> &mdash; %s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Copy_Buckets_Process( $this->as3cf, $this );
	}
}
