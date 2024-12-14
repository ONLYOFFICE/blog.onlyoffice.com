<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Update_ACLs_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Tool;

class Update_ACLs extends Background_Tool {

	/**
	 * @var string
	 */
	protected $tool_key = 'update_acls';

	/**
	 * @var string
	 */
	protected $tab = 'media';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_UPDATE_ACLS_TOOL',
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
		return __( 'Update Object ACLs', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Begin Update', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Updating object ACLs in bucket.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Updating ACLs…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'After disabling Block All Public Access for a bucket, you many need to update all the objects in the bucket to apply appropriate public and private ACLs so that they can be used correctly.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get prompt text for when tool could be run in response to settings change.
	 *
	 * @return string
	 */
	public static function get_prompt_text() {
		global $as3cf;

		$mesg = __( 'It looks like your configuration has changed to require public and private ACLs on objects, would you like to update the ACLs on your objects?', 'amazon-s3-and-cloudfront' );
		$mesg .= ' ';
		$mesg .= $as3cf::more_info_link(
			'/wp-offload-media/doc/block-all-public-access-to-bucket/',
			'update+acls',
			'disable-block-access'
		);

		return $mesg;
	}

	/**
	 * @inheritDoc
	 */
	public function get_doc_url() {
		global $as3cf;

		$args = array( 'utm_campaign' => 'update+acls' );

		return $as3cf::dbrains_url( '/wp-offload-media/doc/block-all-public-access-to-bucket/', $args, 'disable-block-access' );
	}

	/**
	 * Message for error notice.
	 *
	 * @param string|null $message Optional message to override the default for the tool.
	 *
	 * @return string
	 */
	protected function get_error_notice_message( $message = null ) {
		$title   = __( 'Update ACLs Errors', 'amazon-s3-and-cloudfront' );
		$message = empty( $message ) ? __( 'Previous attempts at updating object ACLs have resulted in errors.', 'amazon-s3-and-cloudfront' ) : $message;

		return sprintf( '<strong>%1$s</strong> &mdash; %2$s', $title, $message );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Update_ACLs_Process( $this->as3cf, $this );
	}
}
