<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Move_Objects_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Move_Objects extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'move_objects';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_MOVE_OBJECTS_TOOL',
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
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Move files to new path prefix', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Begin Move', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Moving media items to new path prefix.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Moving…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'Would you like to move your offloaded media files to paths that match the current path prefix settings? All existing offloaded media URLs will be updated to reference the new paths.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_doc_url() {
		global $as3cf;

		$args = array( 'utm_campaign' => 'move+objects' );

		return $as3cf::dbrains_url( '/wp-offload-media/doc/how-to-move-media-to-a-new-bucket-path/', $args );
	}

	/**
	 * Message for error notice.
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Move Objects Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at moving your media library to new paths have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%1$s</strong> &mdash; %2$s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Move_Objects_Process( $this->as3cf, $this );
	}
}
