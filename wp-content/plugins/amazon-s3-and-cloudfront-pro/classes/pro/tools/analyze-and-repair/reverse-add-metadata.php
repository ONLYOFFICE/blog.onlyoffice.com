<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Analyze_And_Repair\Reverse_Add_Metadata_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair;

class Reverse_Add_Metadata extends Analyze_And_Repair {

	/**
	 * @var string
	 */
	protected $tool_key = 'reverse_add_metadata';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_REVERSE_ADD_METADATA_TOOL',
	);

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Remove metadata added by the Add Metadata tool', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'If you have previously used the Add Metadata tool to create new items but now wish to remove all those records, you can use this tool. It will not remove any offload metadata not created with the Add Metadata tool such as regular Media Library offloads.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get button text.
	 *
	 * @return string
	 */
	public function get_button_text() {
		return __( 'Remove Metadata', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Removing metadata added by the Add Metadata tool.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Removing metadata…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Reverse_Add_Metadata_Process( $this->as3cf, $this );
	}
}
