<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Move_Private_Objects_Process extends Move_Objects_Process {

	/**
	 * @var string
	 */
	protected $action = 'move_private_objects';

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
		/** @var Item $class */
		$class         = $this->as3cf->get_source_type_class( $source_type );
		$items_to_move = array();

		if ( $this->as3cf->private_prefix_enabled() ) {
			$new_private_prefix = $this->as3cf->get_setting( 'signed-urls-object-prefix' );
		} else {
			$new_private_prefix = '';
		}

		foreach ( $source_ids as $source_id ) {
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( $as3cf_item ) {
				// Analyze current private prefix to see if it needs changing.
				switch ( $this->should_move_to_new_private_prefix( $as3cf_item, $as3cf_item->private_prefix(), $new_private_prefix ) ) {
					case self::MOVE_NO:
					case self::MOVE_SAME:
						break;
					case self::MOVE_NOOP:
						// If nothing is to be moved to new private prefix, just fix data.
						$as3cf_item->set_private_prefix( $new_private_prefix );
						$as3cf_item->save();
						continue 2;
					case self::MOVE_YES:
						$items_to_move[ $source_id ] = array( 'prefix' => $as3cf_item->normalized_path_dir(), 'private_prefix' => $new_private_prefix );
						break;
				}
			} else {
				$name = $this->as3cf->get_source_type_name( $source_type );
				AS3CF_Error::log( sprintf( 'Move Private Objects: Offload data for %s item with ID %d could not be found for analysis.', $name, $source_id ) );
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
		return __( 'Finished moving media files to new private paths.', 'amazon-s3-and-cloudfront' );
	}
}
