<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use Amazon_S3_And_CloudFront;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Upload_Handler;
use Exception;

class Uploader_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'uploader';

	/**
	 * @var int
	 */
	private $license_limit = -1;

	/**
	 * @var int
	 */
	private $offloaded = 0;

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
		$processed = array();

		// With caching this may be some minutes behind, and may not include previous batches,
		// but this really doesn't matter in the grand scheme of things as it'll eventually catch up.
		$this->license_limit = $this->as3cf->get_total_allowed_media_items_to_upload();

		foreach ( $source_ids as $source_id ) {
			// Check we are allowed to carry on offloading.
			if ( ! $this->should_upload_item( $source_id, $blog_id ) ) {
				return $processed;
			}

			if ( $this->handle_item( $source_type, $source_id, $blog_id ) ) {
				$this->offloaded++;
			}

			// Whether actually offloaded or not, we've processed the item.
			$processed[] = $source_id;
		}

		return $processed;
	}

	/**
	 * Upload the item to provider.
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

		// Skip item if item already on provider.
		if ( $as3cf_item ) {
			return false;
		}

		// Skip if we can't get a valid Item instance.
		$as3cf_item = $class::create_from_source_id( $source_id );
		if ( is_wp_error( $as3cf_item ) ) {
			$this->record_error( $blog_id, $source_type, $source_id, $as3cf_item->get_error_message() );

			return false;
		}

		$upload_handler = $this->as3cf->get_item_handler( Upload_Handler::get_item_handler_key_name() );
		$upload_result  = $upload_handler->handle( $as3cf_item );

		// Build error message.
		if ( is_wp_error( $upload_result ) ) {
			if ( $this->count_errors() < 100 ) {
				foreach ( $upload_result->get_error_messages() as $error_message ) {
					$error_msg = sprintf( __( 'Error offloading to bucket - %s', 'amazon-s3-and-cloudfront' ), $error_message );
					$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Check there is enough allowed items for the license before uploading.
	 *
	 * @param int $item_id
	 * @param int $blog_id
	 *
	 * @return bool
	 */
	protected function should_upload_item( $item_id, $blog_id ) {
		// No limit, or not counting towards limit.
		if ( 0 > $this->license_limit ) {
			return true;
		}

		// If media limit met, cancel the offload and give notice.
		if ( 0 >= ( $this->license_limit - $this->offloaded ) ) {
			// Be really, really sure!
			$this->as3cf->update_media_library_total();
			$this->license_limit = $this->as3cf->get_total_allowed_media_items_to_upload();

			if ( 0 === $this->license_limit ) {
				$this->cancel();

				$notice_id = $this->tool->get_tool_key() . '_license_limit';

				$this->as3cf->notices->undismiss_notice_for_all( $notice_id );

				$args = array(
					'custom_id'         => $notice_id,
					'flash'             => false,
					'only_show_to_user' => false,
				);

				$this->as3cf->notices->add_notice( $this->get_reached_license_limit_message(), $args );

				return false;
			} else {
				// Carry on!
				$this->offloaded = 0;
			}
		}

		return true;
	}

	/**
	 * Get reached license limit notice message.
	 *
	 * @return string
	 */
	protected function get_reached_license_limit_message() {
		$account_link = sprintf( '<a href="%s" target="_blank">%s</a>', $this->as3cf->get_my_account_url(), __( 'My Account', 'amazon-s3-and-cloudfront' ) );
		$notice_msg   = __( "You've reached your license limit so we've had to stop your offload. To offload the rest of your media, please upgrade your license from %s and simply start the offload again. It will start from where it stopped.", 'amazon-s3-and-cloudfront' );

		return sprintf( $notice_msg, $account_link );
	}

	/**
	 * Get blog items to process.
	 *
	 * @param string $source_type Item source type
	 * @param int    $last_source_id
	 * @param int    $limit       Maximum number of item IDs to return
	 * @param bool   $count       Just return the count, negates $limit, default false
	 *
	 * @return array|int
	 */
	protected function get_blog_items( $source_type, $last_source_id, $limit, $count = false ) {
		/** @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		/** @var Item $class */
		$class = $as3cf->get_source_type_class( $source_type );

		if ( ! empty( $class ) ) {
			return $class::get_missing_source_ids( $last_source_id, $limit, $count );
		}

		return $count ? 0 : array();
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
		return __( 'Finished offloading media to bucket.', 'amazon-s3-and-cloudfront' );
	}
}
