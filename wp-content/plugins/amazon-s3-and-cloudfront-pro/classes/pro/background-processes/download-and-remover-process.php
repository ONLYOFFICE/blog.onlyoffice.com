<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Remove_Provider_Handler;
use Exception;

class Download_And_Remover_Process extends Downloader_Process {

	/**
	 * @var string
	 */
	protected $action = 'download_and_remover';

	/**
	 * Download and remove the item from bucket.
	 *
	 * @param string $source_type
	 * @param int    $source_id
	 * @param int    $blog_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function handle_item( $source_type, $source_id, $blog_id ) {
		if ( parent::handle_item( $source_type, $source_id, $blog_id ) ) {
			/** @var Item $class */
			$class      = $this->as3cf->get_source_type_class( $source_type );
			$as3cf_item = $class::get_by_source_id( $source_id );

			/** @var Remove_Provider_Handler $remove_handler */
			$remove_handler = $this->as3cf->get_item_handler( Remove_Provider_Handler::get_item_handler_key_name() );

			// As we've already confirmed that local files exist,
			// and not had to record any errors for display,
			// we can skip confirming that files exist on local,
			// or that the remove from provider succeeded.
			$result = $remove_handler->handle( $as3cf_item, array( 'verify_exists_on_local' => false ) );

			if ( is_wp_error( $result ) ) {
				foreach ( $result->get_error_messages() as $error_message ) {
					$error_msg = sprintf( __( 'Error removing from bucket - %s', 'amazon-s3-and-cloudfront' ), $error_message );
					$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				}

				return false;
			}

			$as3cf_item->delete();

			return true;
		}

		return false;
	}

	/**
	 * Called when background process has been cancelled.
	 */
	protected function cancelled() {
		$this->as3cf->update_media_library_total();
	}

	/**
	 * Called when background process has been paused.
	 */
	protected function paused() {
		$this->as3cf->update_media_library_total();
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
		$this->as3cf->update_media_library_total();
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished removing media files from bucket.', 'amazon-s3-and-cloudfront' );
	}
}
