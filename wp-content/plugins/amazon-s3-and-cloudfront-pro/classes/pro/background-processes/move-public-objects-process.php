<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Move_Public_Objects_Process extends Move_Objects_Process {

	/**
	 * @var string
	 */
	protected $action = 'move_public_objects';

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
		$items_to_move = array();

		/** @var Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		foreach ( $source_ids as $source_id ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( $as3cf_item ) {
				// Analyze current path to see if it needs changing.
				$old_prefix = $as3cf_item->normalized_path_dir();
				$new_prefix = $this->get_new_public_prefix( $as3cf_item, $old_prefix );

				if ( $new_prefix !== $old_prefix ) {
					$items_to_move[ $source_id ] = array( 'prefix' => $new_prefix, 'private_prefix' => $as3cf_item->private_prefix() );
				}
			} else {
				$name = $this->as3cf->get_source_type_name( $source_type );
				AS3CF_Error::log( sprintf( 'Move Public Objects: Offload data for %s item with ID %d could not be found for analysis.', $name, $source_id ) );
			}
		}

		$this->move_items( $items_to_move, $blog_id, $source_type );

		// Whether moved or not, we processed every item.
		return $source_ids;
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished moving media files to new storage paths.', 'amazon-s3-and-cloudfront' );
	}
}
