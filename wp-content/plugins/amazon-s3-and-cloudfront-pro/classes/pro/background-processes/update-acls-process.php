<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Update_ACLs_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'update_acls';

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
		$bucket = $this->as3cf->get_setting( 'bucket' );
		$region = $this->as3cf->get_setting( 'region' );

		/** @var Item $class */
		$class                 = $this->as3cf->get_source_type_class( $source_type );
		$attachments_to_update = array();

		foreach ( $source_ids as $source_id ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( empty( $as3cf_item ) ) {
				$name = $this->as3cf->get_source_type_name( $source_type );
				AS3CF_Error::log( sprintf( 'Update Object ACLs: Offload data for %s item with ID %d could not be found for analysis.', $name, $source_id ) );
				continue;
			}

			// If the attachment is offloaded to another provider, skip it.
			if ( ! $as3cf_item->served_by_provider( true ) ) {
				continue;
			}

			// If the attachment is offloaded to another bucket, skip it, because we don't know its Block Public Access state.
			if ( $as3cf_item->bucket() !== $bucket ) {
				continue;
			}
			$attachments_to_update[] = $source_id;
		}

		$this->update_items( $source_type, $attachments_to_update, $blog_id, $bucket, $region );

		// Whether updated or not, we processed every item.
		return $source_ids;
	}

	/**
	 * Bulk update ACLs for items.
	 *
	 * @param string $source_type
	 * @param array  $items
	 * @param int    $blog_id
	 * @param string $bucket
	 * @param string $region
	 *
	 * @throws Exception
	 */
	protected function update_items( $source_type, $items, $blog_id, $bucket, $region ) {
		if ( empty( $items ) ) {
			return;
		}

		$keys = $this->as3cf->get_provider_keys( $items, $source_type );

		if ( empty( $keys ) ) {
			return;
		}

		/** @var Item $class */
		$class           = $this->as3cf->get_source_type_class( $source_type );
		$items_to_update = array();

		foreach ( $keys as $source_id => $source_keys ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			foreach ( $source_keys as $key ) {
				$size = $as3cf_item->get_object_key_from_filename( $key );
				$acl  = $as3cf_item->get_acl_for_object_key( $size );

				// Only set ACL if actually required, some storage provider and bucket settings disable changing ACL.
				// This is a fallback check, just in case settings changed from under us via define etc, saves throwing lots of errors.
				if ( ! empty( $acl ) ) {
					$items_to_update[] = array(
						'Bucket' => $bucket,
						'Key'    => $key,
						'ACL'    => $acl,
					);
				}
			}
		}

		$failures = array();

		if ( ! empty( $items_to_update ) ) {
			$client = $this->as3cf->get_provider_client( $region, true );
			try {
				$failures = $client->update_object_acls( $items_to_update );
			} catch ( Exception $e ) {
				AS3CF_Error::log( $e->getMessage() );

				return;
			}
		}

		if ( ! empty( $failures ) ) {
			$this->record_failures( $keys, $failures, $blog_id, $source_type );
		}
	}

	/**
	 * Handle failed keys.
	 *
	 * @param array  $keys
	 * @param array  $failures
	 * @param int    $blog_id
	 * @param string $source_type
	 */
	protected function record_failures( $keys, $failures, $blog_id, $source_type ) {
		foreach ( $failures as $failure ) {
			foreach ( $keys as $source_id => $source_keys ) {
				if ( false !== array_search( $failure['Key'], $source_keys ) ) {
					$error_msg = sprintf( __( 'Error updating object ACL for %1$s: %2$s', 'amazon-s3-and-cloudfront' ), $failure['Key'], $failure['Message'] );

					$this->record_error( $blog_id, $source_type, $source_id, $error_msg );

					unset( $keys[ $source_id ] );

					break;
				}
			}
		}
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished updating object ACLs in bucket.', 'amazon-s3-and-cloudfront' );
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
