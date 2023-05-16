<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Copy_Buckets_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'copy_buckets';

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
		$class  = $this->as3cf->get_source_type_class( $source_type );

		$items_to_copy = array();

		foreach ( $source_ids as $source_id ) {
			/** @var Item $class */
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( false === $as3cf_item || is_wp_error( $as3cf_item ) ) {
				continue;
			}

			if ( $as3cf_item->bucket() === $bucket ) {
				continue;
			}

			$items_to_copy[] = $source_id;
		}

		$this->copy_items( $items_to_copy, $blog_id, $bucket, $region, $source_type );

		// Whether copied or not, we processed every item.
		return $source_ids;
	}

	/**
	 * Copy items to new bucket.
	 *
	 * @param array  $items
	 * @param int    $blog_id
	 * @param string $bucket
	 * @param string $region
	 * @param string $source_type
	 *
	 * @throws Exception
	 */
	protected function copy_items( $items, $blog_id, $bucket, $region, $source_type ) {
		if ( empty( $items ) ) {
			return;
		}

		$keys = $this->as3cf->get_provider_keys( $items, $source_type );

		if ( empty( $keys ) ) {
			return;
		}

		$items_to_copy = array();
		$skipped       = array();

		/** @var  Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		foreach ( $keys as $source_id => $source_keys ) {
			/** @var Item $as3cf_item */
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( ! $as3cf_item->served_by_provider( true ) ) {
				$name      = $this->as3cf->get_source_type_name( $source_type );
				$skipped[] = array(
					'Key'     => $source_keys[0],
					'Message' => sprintf( __( '%s item with ID %s is offloaded to a different provider than currently configured', 'amazon-s3-and-cloudfront' ), $name, $source_id ),
				);
				continue;
			}

			foreach ( $source_keys as $key ) {

				$args = array(
					'Bucket'     => $bucket,
					'Key'        => $key,
					'CopySource' => urlencode( "{$as3cf_item->bucket()}/{$key}" ),
				);

				$size = $as3cf_item->get_object_key_from_filename( $key );
				$acl  = $as3cf_item->get_acl_for_object_key( $size, $bucket );

				// Only set ACL if actually required, some storage provider and bucket settings disable changing ACL.
				if ( ! empty( $acl ) ) {
					$args['ACL'] = $acl;
				}

				/**
				 * Filter documented in Items\Upload_Handler::pre_handle
				 */
				$args = apply_filters( 'as3cf_object_meta', $args, $source_id, $size, true, $as3cf_item->get_item_source_array() );

				// Protect against filter use and only set ACL if actually required, some storage provider and bucket settings disable changing ACL.
				if ( isset( $args['ACL'] ) && empty( $acl ) ) {
					unset( $args['ACL'] );
				}

				$items_to_copy[] = $args;
			}
		}

		$failures = array();

		if ( ! empty( $items_to_copy ) ) {
			$client = $this->as3cf->get_provider_client( $region, true );
			try {
				$failures = $client->copy_objects( $items_to_copy );
			} catch ( Exception $e ) {
				AS3CF_Error::log( $e->getMessage() );

				return;
			}
		}

		$failures = $failures + $skipped;

		if ( ! empty( $failures ) ) {
			$keys = $this->handle_failed_keys( $keys, $failures, $blog_id, $source_type );
		}

		$this->update_item_provider_info( $keys, $bucket, $region, $source_type );
	}

	/**
	 * Handle failed keys.
	 *
	 * @param array  $keys
	 * @param array  $failures
	 * @param int    $blog_id
	 * @param string $source_type
	 *
	 * @return array
	 */
	protected function handle_failed_keys( $keys, $failures, $blog_id, $source_type ) {
		foreach ( $failures as $failure ) {
			foreach ( $keys as $source_id => $source_keys ) {
				if ( false !== array_search( $failure['Key'], $source_keys ) ) {
					$error_msg = sprintf( __( 'Error copying %s between buckets: %s', 'amazon-s3-and-cloudfront' ), $failure['Key'], $failure['Message'] );

					$this->record_error( $blog_id, $source_type, $source_id, $error_msg );

					unset( $keys[ $source_id ] );

					break;
				}
			}
		}

		return $keys;
	}

	/**
	 * Update item provider info.
	 *
	 * @param array  $keys
	 * @param string $bucket
	 * @param string $region
	 * @param string $source_type
	 */
	protected function update_item_provider_info( $keys, $bucket, $region, $source_type ) {
		if ( empty( $keys ) ) {
			return;
		}

		/** @var Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		foreach ( $keys as $source_id => $source_keys ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			$as3cf_item->set_region( $region );
			$as3cf_item->set_bucket( $bucket );
			$as3cf_item->save();
		}
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished copying media files to new bucket.', 'amazon-s3-and-cloudfront' );
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
