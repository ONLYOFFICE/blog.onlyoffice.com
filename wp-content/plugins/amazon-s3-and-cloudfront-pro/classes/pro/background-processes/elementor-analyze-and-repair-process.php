<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use Exception;

class Elementor_Analyze_And_Repair_Process extends Background_Tool_Process {

	/**
	 * @var string
	 */
	protected $action = 'elementor_analyze_and_repair';

	/**
	 * Process chunk of posts
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
		foreach ( $source_ids as $source_id ) {
			$this->handle_item( $source_type, $source_id, $blog_id );
		}

		// We processed every item.
		return $source_ids;
	}

	/**
	 * Process individual posts/pages with Elementor data and update any remote
	 * URLs to the corresponding local version.
	 *
	 * @param string $source_type
	 * @param int    $post_id
	 * @param int    $blog_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function handle_item( $source_type, $post_id, $blog_id ) {
		$original_json = get_post_meta( $post_id, '_elementor_data', true );
		$skip_values   = array( 'true', 'false', 'null' );

		if ( empty( $original_json ) || ( is_string( $original_json ) && in_array( trim( $original_json ), $skip_values, true ) ) ) {
			return true;
		}

		// We should always get data in JSON format, that's what Elementor saves it as. In tests
		// we've seen it stored as a serialized array in a few cases. Elementor can read both.
		// For good measure we test and convert if needed.
		if ( is_array( $original_json ) ) {
			$original_json = wp_json_encode( $original_json );
			if ( false === $original_json ) {
				$error_msg = sprintf( __( 'Existing elementor data for post - %d contains a serialized array that could not be converted to JSON - skipping', 'amazon-s3-and-cloudfront' ), $post_id );
				AS3CF_Error::log( $error_msg );

				return false;
			}
		}

		// Verify that the original post meta contains valid JSON
		if ( is_string( $original_json ) ) {
			$decoded_original = json_decode( $original_json, true );
			if ( is_null( $decoded_original ) ) {
				$error_msg = sprintf( __( 'Existing elementor data for post - %d contains invalid JSON - skipping', 'amazon-s3-and-cloudfront' ), $post_id );
				AS3CF_Error::log( $error_msg );

				return false;
			}
		}

		$modified_json = $original_json;
		$modified_json = str_replace( '\/', '/', $modified_json );
		$modified_json = $this->as3cf->filter_provider->filter_post( $modified_json );

		// Verify that we still have valid JSON
		$decoded = json_decode( $modified_json, true );
		if ( is_null( $decoded ) ) {
			$error_msg = sprintf( __( 'Error replacing URLs in Elementor data for post - %d results in invalid JSON', 'amazon-s3-and-cloudfront' ), $post_id );
			AS3CF_Error::log( $error_msg );

			return false;
		}

		// Verify that the JSON can be re-encoded
		$modified_json = wp_json_encode( $decoded );
		if ( false === $modified_json ) {
			$error_msg = sprintf( __( 'Error replacing URLs in Elementor data for post - %d JSON re-encoding failed', 'amazon-s3-and-cloudfront' ), $post_id );
			AS3CF_Error::log( $error_msg );

			return false;
		}

		if ( $modified_json !== $original_json ) {
			update_post_meta( $post_id, '_elementor_data', wp_slash( $modified_json ) );
		}

		return true;
	}

	/**
	 * Return the count of Elementor posts
	 *
	 * @return int
	 */
	public function get_elementor_items_count() {
		return $this->get_blog_items( Media_Library_Item::source_type(), null, null, true );
	}

	/**
	 * Find all items in the Posts table that are created with Elementor
	 *
	 * @param string $source_type Item source type
	 * @param int    $last_post_id
	 * @param int    $limit       Maximum number of posts to return
	 * @param bool   $count       Just return the count, negates $limit, default false
	 *
	 * @return array|int
	 */
	protected function get_blog_items( $source_type, $last_post_id, $limit, $count = false ) {
		global $wpdb;

		$args = array();

		if ( $count ) {
			$sql = 'SELECT COUNT(DISTINCT p.id)';
		} else {
			$sql = 'SELECT DISTINCT p.id';
		}

		$sql .= " FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.id = m.post_id";
		$sql .= " WHERE m.meta_key = '_elementor_data' AND p.post_status != 'inherit' ";

		if ( is_numeric( $last_post_id ) ) {
			$sql    .= ' AND p.id < %d';
			$args[] = $last_post_id;
		}

		if ( ! $count ) {
			$sql    .= ' ORDER BY p.id DESC LIMIT %d';
			$args[] = $limit;
		}

		if ( count( $args ) > 0 ) {
			$sql = $wpdb->prepare( $sql, $args );
		}

		if ( $count ) {
			return $wpdb->get_var( $sql );
		} else {
			return array_map( 'intval', $wpdb->get_col( $sql ) );
		}
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

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished the Elementor Analyze and Repair process.', 'amazon-s3-and-cloudfront' );
	}
}
