<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use Exception;

class Add_Metadata_Process extends Uploader_Process {

	/**
	 * @var string
	 */
	protected $action = 'add_metadata';

	/**
	 * Increased batch limit as there is no network or file handling.
	 *
	 * @var int
	 */
	protected $limit = 2000;

	/**
	 * Increased chunk size as there is no network or file handling.
	 *
	 * @var int
	 */
	protected $chunk = 100;

	/**
	 * Create metadata as if item has been offloaded to provider, but don't actually do an offload.
	 *
	 * @param string $source_type
	 * @param int    $source_id
	 * @param int    $blog_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function handle_item( $source_type, $source_id, $blog_id ) {
		// Skip item if already on provider.
		if ( Media_Library_Item::get_by_source_id( $source_id ) ) {
			return false;
		}

		$options    = array(
			'originator'            => Item::ORIGINATORS['metadata-tool'],
			'is_verified'           => false,
			'use_object_versioning' => false,
		);
		$as3cf_item = Media_Library_Item::create_from_source_id( $source_id, $options );

		// Build error message
		if ( is_wp_error( $as3cf_item ) ) {
			foreach ( $as3cf_item->get_error_messages() as $error_message ) {
				$error_msg = sprintf( __( 'Error adding metadata - %s', 'amazon-s3-and-cloudfront' ), $error_message );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
			}

			return false;
		} else {
			$as3cf_item->save();
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
		$notice_msg   = __( "You've reached your license limit so we've had to stop adding metadata. To add metadata to the rest of your Media Library, please upgrade your license from %s and simply start the add metadata tool again. It will start from where it stopped.", 'amazon-s3-and-cloudfront' );

		return sprintf( $notice_msg, $account_link );
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished adding metadata to Media Library.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Called when background process has completed.
	 */
	protected function completed() {
		$this->as3cf->update_media_library_total();
	}
}
