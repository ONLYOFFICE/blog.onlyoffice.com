<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use DeliciousBrains\WP_Offload_Media\Items\Download_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Downloader_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'downloader';

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
			$this->handle_item( $source_type, $source_id, $blog_id );
		}

		// Whether downloaded to local or not, we processed every item.
		return $processed;
	}

	/**
	 * Download the item from bucket.
	 *
	 * @param string $source_type
	 * @param int    $source_id
	 * @param int    $blog_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function handle_item( $source_type, $source_id, $blog_id ) {
		/** @var Item $class */
		$class      = $this->as3cf->get_source_type_class( $source_type );
		$as3cf_item = $class::get_by_source_id( $source_id );

		if ( ! $as3cf_item ) {
			return false;
		}

		/** @var Download_Handler $download_handler */
		$download_handler = $this->as3cf->get_item_handler( Download_Handler::get_item_handler_key_name() );
		$result           = $download_handler->handle( $as3cf_item );

		if ( is_wp_error( $result ) ) {
			foreach ( $result->get_error_messages() as $error_message ) {
				$error_msg = sprintf( __( 'Error downloading to server - %s', 'amazon-s3-and-cloudfront' ), $error_message );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
			}

			return false;
		}

		return true;
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished downloading media files to local server.', 'amazon-s3-and-cloudfront' );
	}

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
