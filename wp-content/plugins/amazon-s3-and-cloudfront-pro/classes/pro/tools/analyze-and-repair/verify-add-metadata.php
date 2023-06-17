<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair;

use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Analyze_And_Repair\Verify_Add_Metadata_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Background_Tool_Process;
use DeliciousBrains\WP_Offload_Media\Pro\Tools\Analyze_And_Repair;

class Verify_Add_Metadata extends Analyze_And_Repair {

	/**
	 * @var string
	 */
	protected $tool_key = 'verify_add_metadata';

	/**
	 * @var array
	 */
	protected static $show_tool_constants = array(
		'AS3CF_SHOW_VERIFY_ADD_METADATA_TOOL',
	);

	/**
	 * Get title text.
	 *
	 * @return string
	 */
	public function get_title_text() {
		return __( 'Find items with files missing in bucket and remove metadata, mark others verified', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get more info text.
	 *
	 * @return string
	 */
	public static function get_more_info_text() {
		return __( 'You can use this tool to check that files exist in the bucket for items recently created with the Add Metadata tool. New metadata items with files missing from the bucket will be removed. It will not remove any offload metadata not created with the Add Metadata tool such as regular Media Library offloads.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get queued status text.
	 *
	 * @return string
	 */
	public function get_queued_status(): string {
		return __( 'Finding items with files missing in bucket and removing their metadata, marking others as verified.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get short queued status text.
	 *
	 * @return string
	 */
	public function get_short_queued_status(): string {
		return _x( 'Verifying…', 'Short tool running message', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get background process class.
	 *
	 * @return Background_Tool_Process|null
	 */
	protected function get_background_process_class() {
		return new Verify_Add_Metadata_Process( $this->as3cf, $this );
	}
}
