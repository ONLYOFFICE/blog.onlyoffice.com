<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Move_Private_Objects_Process;

class Move_Private_Objects extends Move_Objects {

	/**
	 * @var string
	 */
	protected $tool_key = 'move_private_objects';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_MOVE_PRIVATE_OBJECTS_TOOL',
	);

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Move files to new private path', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Moving media items to new private path.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'Would you like to move your offloaded media files to paths that match the current private path settings? All existing offloaded private media URLs will be updated to reference the new paths.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = '<h3>' . __( 'Private Path Updated: Would you like to move existing media to the new private path?', 'amazon-s3-and-cloudfront' ) . '</h3>';
		$mesg .= '<br>';
		$mesg .= '<p>' . __( 'You just updated the private media path. Any media you make private from now on will use this new path.', 'amazon-s3-and-cloudfront' ) . '</p>';
		$mesg .= '<p>';
		$mesg .= __( 'You can also move existing private media to this new path. We recommend keeping the path consistent across all private media.', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::more_info_link(
			'/wp-offload-media/doc/how-to-move-media-to-a-new-bucket-path/',
			'move+objects',
			'private-path'
		);
		$mesg .= '</p>';

		return $mesg;
	}

	/**
	 * @inheritDoc
	 */
	public function get_doc_url() {
		global $as3cf;

		$args = array( 'utm_campaign' => 'move+objects' );

		return $as3cf::dbrains_url( '/wp-offload-media/doc/how-to-move-media-to-a-new-bucket-path/', $args, 'private-path' );
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
		$message = empty( $message ) ? __( 'Previous attempts at moving your media library to new private paths have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%1$s</strong> &mdash; %2$s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Move_Private_Objects_Process( $this->as3cf, $this );
	}
}
