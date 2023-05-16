<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Remove_Local_Handler;
use Exception;

class Remove_Local_Files_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'remove_local_files';

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
		$processed            = $source_ids;
		$remove_local_handler = $this->as3cf->get_item_handler( Remove_Local_Handler::get_item_handler_key_name() );

		/** @var Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		foreach ( $source_ids as $source_id ) {
			/** @var Item $as3cf_item */
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( empty( $as3cf_item ) ) {
				continue;
			}

			if ( ! $as3cf_item->served_by_provider( true ) ) {
				continue;
			}

			if ( ! $as3cf_item->exists_locally() ) {
				continue;
			}

			$remove_local_handler->handle( $as3cf_item );
		}

		// Whether removed from local or not, we processed every item.
		return $processed;
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished removing media files from local server.', 'amazon-s3-and-cloudfront' );
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
