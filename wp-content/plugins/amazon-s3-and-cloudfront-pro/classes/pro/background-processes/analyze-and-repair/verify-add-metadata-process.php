<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Analyze_And_Repair;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Pro\Background_Processes\Analyze_And_Repair_Process;
use Exception;
use WP_Error;

class Verify_Add_Metadata_Process extends Analyze_And_Repair_Process {

	/**
	 * @var string
	 */
	protected $action = 'verify_add_metadata';

	/**
	 * Get blog items to process.
	 *
	 * @param string $source_type    Item source type
	 * @param int    $last_source_id The ID of the last item previously processed
	 * @param int    $limit          Maximum number of item IDs to return
	 * @param bool   $count          Just return the count, negates $limit, default false
	 *
	 * @return array|int
	 */
	protected function get_blog_items( $source_type, $last_source_id, $limit, $count = false ) {
		return Media_Library_Item::get_source_ids( $last_source_id, $limit, $count, Item::ORIGINATORS['metadata-tool'], false );
	}

	/**
	 * Performs required analysis and repairs for given offloaded item.
	 *
	 * @param Media_Library_Item $as3cf_item
	 *
	 * @return bool|WP_Error Returns false if no action required, true if repaired, or WP_Error if could not be processed or repaired.
	 */
	protected function analyze_and_repair( Media_Library_Item $as3cf_item ) {
		$bucket = $as3cf_item->bucket();

		if ( empty( $bucket ) ) {
			return new WP_Error( 'exception', 'Could not get bucket for item.' );
		}

		$region = $as3cf_item->region();

		if ( is_wp_error( $region ) ) {
			return new WP_Error( 'exception', 'Could not get region for bucket: ' . $region->get_error_message() );
		} elseif ( ! is_string( $region ) ) {
			return new WP_Error( 'exception', "Could not get region for item's bucket." );
		}

		try {
			$provider_client = $this->as3cf->get_provider_client( $region );
		} catch ( Exception $e ) {
			return new WP_Error( 'exception', 'Could not get provider client: ' . $e->getMessage() );
		}

		$paths = AS3CF_Utils::get_attachment_file_paths( $as3cf_item->source_id(), false );

		if ( empty( $paths ) ) {
			return new WP_Error( 'exception', 'Could not get paths for Media Library item with ID: ' . $as3cf_item->source_id() );
		}

		$fullsize_paths = AS3CF_Utils::fullsize_paths( $paths );

		if ( empty( $fullsize_paths ) ) {
			return new WP_Error( 'exception', 'Could not get full size paths for Media Library item with ID: ' . $as3cf_item->source_id() );
		}

		$fullsize_exists  = false;
		$fullsize_missing = false;

		foreach ( $fullsize_paths as $path ) {
			$key = $as3cf_item->key( wp_basename( $path ) );

			if ( $provider_client->does_object_exist( $bucket, $key ) ) {
				$fullsize_exists = true;
			} else {
				$fullsize_missing = true;
			}
		}

		// A full sized file has not been found, remove metadata.
		if ( ! $fullsize_exists ) {
			$as3cf_item->delete();

			return false;
		}

		// At least one full sized file has been found, set as verified.
		$as3cf_item->set_is_verified( true );
		$as3cf_item->save();

		// Need to log that sizes need regeneration?
		// NOTE: As we currently do not have a means of setting individual thumbnails sizes as verified, we can shortcut out here.
		// NOTE: In the future we should record whether each size is verified and/or remove details record.
		if ( $fullsize_missing ) {
			return new WP_Error( 'exception', 'Thumbnails need regenerating for Media Library item with ID: ' . $as3cf_item->source_id() );
		}

		// If item has sizes, check them too.
		$size_paths = array_diff( $paths, $fullsize_paths );

		if ( empty( $size_paths ) ) {
			return true;
		}

		foreach ( $size_paths as $path ) {
			$key = $as3cf_item->key( wp_basename( $path ) );

			if ( ! $provider_client->does_object_exist( $bucket, $key ) ) {
				return new WP_Error( 'exception', 'Thumbnails need regenerating for Media Library item with ID: ' . $as3cf_item->source_id() );
			}
		}

		return true;
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished checking or removing items previously created with the Add Metadata tool.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Called when background process has completed.
	 */
	protected function completed() {
		$this->as3cf->update_media_library_total();
	}
}
