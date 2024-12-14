<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use Exception;
use WP_Error;

abstract class Analyze_And_Repair_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'analyze_and_repair';

	/**
	 * Process items chunk.
	 *
	 * @param string $source_type
	 * @param array  $source_ids
	 * @param int    $blog_id
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	protected function process_items_chunk( $source_type, $source_ids, $blog_id ) {
		$processed = $source_ids;

		foreach ( $source_ids as $source_id ) {
			if ( $this->as3cf->is_attachment_served_by_provider( $source_id, true, true ) ) {
				$this->handle_item( $source_type, $source_id, $blog_id );
			}
		}

		// Whether handled or not, we processed every item.
		return $processed;
	}

	/**
	 * Analyze and repair each item's offload metadata and log any errors.
	 *
	 * @param string $source_type
	 * @param int    $source_id
	 * @param int    $blog_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function handle_item( $source_type, $source_id, $blog_id ) {
		$as3cf_item = Media_Library_Item::get_by_source_id( $source_id );

		if ( empty( $as3cf_item ) ) {
			return false;
		}

		$result = $this->analyze_and_repair( $as3cf_item );

		// Build generic error message.
		if ( is_wp_error( $result ) ) {
			foreach ( $result->get_error_messages() as $error_message ) {
				$error_msg = sprintf( __( 'Error - %s', 'amazon-s3-and-cloudfront' ), $error_message );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
			}

			return false;
		}

		return true;
	}

	/**
	 * Performs required analysis and repairs for given offloaded item.
	 *
	 * @param Media_Library_Item $as3cf_item
	 *
	 * @return bool|WP_Error Returns false if no action required, true if repaired, or WP_Error if could not be processed or repaired.
	 */
	abstract protected function analyze_and_repair( Media_Library_Item $as3cf_item );

	/**
	 * Called when background process has been cancelled.
	 */
	protected function cancelled() {
		// Do nothing at the moment.
	}

	/**
	 * Called when background process has been paused.
	 */
	protected function paused() {
		// Do nothing at the moment.
	}

	/**
	 * Called when background process has been resumed.
	 */
	protected function resumed() {
		// Do nothing at the moment.
	}

	/**
	 * Called when background process has completed.
	 */
	protected function completed() {
		// Do nothing at the moment.
	}
}
