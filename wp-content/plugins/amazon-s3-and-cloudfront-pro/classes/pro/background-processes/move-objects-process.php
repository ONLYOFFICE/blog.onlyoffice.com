<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Background_Processes;

use AS3CF_Error;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Exception;

class Move_Objects_Process extends Background_Tool_Process {

	const MOVE_NO   = 0;
	const MOVE_YES  = 1;
	const MOVE_SAME = 2;
	const MOVE_NOOP = 3;

	/**
	 * @var string
	 */
	protected $action = 'move_objects';

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
			$new_private_prefix = $this->as3cf->get_setting( 'signed-urls-object-prefix', '' );
		} else {
			$new_private_prefix = '';
		}

		foreach ( $source_ids as $source_id ) {
			$update     = false;
			$as3cf_item = $class::get_by_source_id( $source_id );

			if ( $as3cf_item ) {
				// Analyze current path to see if it needs changing.
				$old_prefix = $as3cf_item->normalized_path_dir();
				$new_prefix = $this->get_new_public_prefix( $as3cf_item, $old_prefix );

				if ( $new_prefix !== $old_prefix ) {
					$update = true;
				}

				// Analyze current private prefix to see if it needs changing.
				$private_prefix = $as3cf_item->private_prefix();

				switch ( $this->should_move_to_new_private_prefix( $as3cf_item, $private_prefix, $new_private_prefix ) ) {
					case self::MOVE_NO:
					case self::MOVE_SAME:
						break;
					case self::MOVE_NOOP:
						// If nothing is to be moved to new private prefix, and public isn't being updated, just fix data.
						if ( false === $update ) {
							$as3cf_item->set_private_prefix( $new_private_prefix );
							$as3cf_item->save();
							continue 2;
						}

						$private_prefix = $new_private_prefix;
						break;
					case self::MOVE_YES:
						$private_prefix = $new_private_prefix;
						$update         = true;
						break;
				}

				if ( $update ) {
					$items_to_move[ $source_id ] = array( 'prefix' => $new_prefix, 'private_prefix' => $private_prefix );
				}
			} else {
				$name = $this->as3cf->get_source_type_name( $source_type );
				AS3CF_Error::log( sprintf( 'Move Objects: Offload data for %s item with ID %d could not be found for analysis.', $name, $source_id ) );
			}
		}

		$this->move_items( $items_to_move, $blog_id, $source_type );

		// Whether moved or not, we processed every item.
		return $source_ids;
	}

	/**
	 * Returns new public prefix if required, otherwise returns old prefix.
	 *
	 * phpcs:disable Generic.PHP.DiscourageGoto.Found
	 *
	 * @param Item   $as3cf_item
	 * @param string $old_prefix
	 *
	 * @return string
	 */
	protected function get_new_public_prefix( Item $as3cf_item, $old_prefix ) {
		$new_prefix = $as3cf_item->get_new_item_prefix();

		// Length changed is simplest indicator.
		if ( strlen( $old_prefix ) !== strlen( $new_prefix ) ) {
			goto move_item;
		}

		$old_parts = explode( '/', trim( $old_prefix, '/' ) );
		$new_parts = explode( '/', trim( $new_prefix, '/' ) );

		// Number of path elements changed?
		if ( count( $old_parts ) !== count( $new_parts ) ) {
			goto move_item;
		}

		// If object versioning is on, don't compare last segment.
		if ( $this->as3cf->get_setting( 'object-versioning', false ) && $as3cf_item->can_use_object_versioning() ) {
			$old_parts = array_slice( $old_parts, 0, -1 );
			$new_parts = array_slice( $new_parts, 0, -1 );
		}

		// Each element should now be the same.
		// Simplest way to check here is walk one and check the other by index.
		// No need to get all fancy!
		foreach ( $old_parts as $key => $val ) {
			if ( $new_parts[ $key ] !== $val ) {
				goto move_item;
			}
		}

		// If here, then prefix does not need to change, regardless of whether private prefix does.
		// This could be important for mixed public/private thumbnails and external links.
		// We already know that old and new prefix are the same except for object version,
		// which is at least still using the same format (length check confirmed that).
		$new_prefix = $old_prefix;

		move_item:

		return $new_prefix;
	}

	/**
	 * Should the given item be moved to the new private prefix?
	 *
	 * @param Item   $as3cf_item
	 * @param string $old_private_prefix
	 * @param string $new_private_prefix
	 *
	 * @return int One of MOVE_NO, MOVE_YES, MOVE_SAME or MOVE_NOOP.
	 */
	protected function should_move_to_new_private_prefix( Item $as3cf_item, $old_private_prefix, $new_private_prefix ) {
		// Analyze current private prefix to see if it needs changing.
		if ( $old_private_prefix === $new_private_prefix ) {
			// Private prefix not changed, nothing to do.
			return self::MOVE_SAME;
		} elseif ( ! $as3cf_item->is_private() && ! $as3cf_item->has_private_objects() ) {
			// Not same, but nothing is to be moved to private prefix, maybe just fix data.
			return self::MOVE_NOOP;
		} else {
			// Private prefix changed, move some private objects.
			return self::MOVE_YES;
		}
	}

	/**
	 * Move items to new path.
	 *
	 * @param array  $items id => ['prefix' => 'new/path/prefix', 'private_prefix' => 'private']
	 * @param int    $blog_id
	 * @param string $source_type
	 *
	 * @throws Exception
	 *
	 * Note: `private_prefix` will be prepended to `prefix` for any object that is private.
	 *       `prefix` and `private_prefix` are "directory" paths and can have leading/trailing slashes, they'll be handled.
	 *       Both `prefix` and `private_prefix` must be set per item id, but either/both may be empty.
	 */
	protected function move_items( $items, $blog_id, $source_type ) {
		if ( empty( $items ) ) {
			return;
		}

		$bucket = $this->as3cf->get_setting( 'bucket' );
		$region = $this->as3cf->get_setting( 'region' );

		if ( empty( $bucket ) ) {
			return;
		}

		/** @var Item $class */
		$class            = $this->as3cf->get_source_type_class( $source_type );
		$source_type_name = $this->as3cf->get_source_type_name( $source_type );
		$keys             = array();
		$new_keys         = array();
		$items_to_move    = array();

		foreach ( array_keys( $items ) as $source_id ) {
			/** @var Item $as3cf_item */
			$as3cf_item  = $class::get_by_source_id( $source_id );
			$source_keys = array_unique( $as3cf_item->provider_keys() );

			// If the item isn't served by this provider, skip it.
			if ( ! $as3cf_item->served_by_provider( true ) ) {
				$error_msg = sprintf( __( '% ID %s is offloaded to a different provider than currently configured', 'amazon-s3-and-cloudfront' ), $source_type_name, $source_id );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				continue;
			}

			// If the prefix isn't set, skip it.
			if ( ! isset( $items[ $source_id ]['prefix'] ) ) {
				$error_msg = sprintf( __( 'Prefix not set for % ID %s (this should never happen, please report to support)', 'amazon-s3-and-cloudfront' ), $source_type_name, $source_id );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				continue;
			}

			// If the private_prefix isn't set, skip it.
			if ( ! isset( $items[ $source_id ]['private_prefix'] ) ) {
				$error_msg = sprintf( __( 'Private prefix not set for %s ID %s (this should never happen, please report to support)', 'amazon-s3-and-cloudfront' ), $source_type_name, $source_id );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				continue;
			}

			// If the item is offloaded to another bucket, skip it.
			if ( $as3cf_item->bucket() !== $bucket ) {
				$error_msg = sprintf( __( '%s ID %s is offloaded to a different bucket than currently configured', 'amazon-s3-and-cloudfront' ), $source_type_name, $source_id );
				$this->record_error( $blog_id, $source_type, $source_id, $error_msg );
				continue;
			}

			$updated_item = clone $as3cf_item;

			$updated_item->set_private_prefix( AS3CF_Utils::trailingslash_prefix( $items[ $source_id ]['private_prefix'] ) );
			$updated_item->update_path_prefix( AS3CF_Utils::trailingslash_prefix( $items[ $source_id ]['prefix'] ) );

			// TODO: Make sure we're not clobbering another item's path.

			// Each key found in old paths will be moved to new path as appropriate for access.
			foreach ( $source_keys as $object_key => $key ) {
				$new_key = $updated_item->provider_key( $object_key );

				// If the old and new key are the same, don't try and move it.
				if ( $key === $new_key ) {
					continue;
				}

				// We need to record the old and new key so that we can reconcile them later.
				$keys[ $source_id ][]     = $key;
				$new_keys[ $source_id ][] = $new_key;

				$args = array(
					'Bucket'     => $as3cf_item->bucket(),
					'Key'        => $new_key,
					'CopySource' => urlencode( "{$as3cf_item->bucket()}/{$key}" ),
				);

				$acl = $as3cf_item->get_acl_for_object_key( $object_key );

				// Only set ACL if actually required, some storage provider and bucket settings disable changing ACL.
				if ( ! empty( $acl ) ) {
					$args['ACL'] = $acl;
				}

				/**
				 * Filter documented in Upload_Handler::pre_handle
				 */
				$args = apply_filters( 'as3cf_object_meta', $args, $source_id, $object_key, false, $as3cf_item->get_item_source_array() );

				// Protect against filter use and only set ACL if actually required, some storage provider and bucket settings disable changing ACL.
				if ( isset( $args['ACL'] ) && empty( $acl ) ) {
					unset( $args['ACL'] );
				}

				$items_to_move[] = $args;
			}
		}

		// All skipped, abort.
		if ( empty( $items_to_move ) ) {
			return;
		}

		/*
		 * As there is no such thing as "move" objects in supported providers, and we want to be able to roll-back
		 * an entire item's copies if any fail, we copy, check for failures, and then only delete old keys
		 * which have successfully copied. Any partially copied item have their successful copies deleted
		 * instead so as to not leave orphaned objects either with old or new key prefixes.
		 */

		$client = $this->as3cf->get_provider_client( $region, true );

		try {
			$failures = $client->copy_objects( $items_to_move );
		} catch ( Exception $e ) {
			AS3CF_Error::log( $e->getMessage() );

			return;
		}

		if ( ! empty( $failures ) ) {
			$keys_to_remove = $this->handle_failed_keys( $keys, $failures, $blog_id, $source_type, $new_keys );
		} else {
			$keys_to_remove = $keys;
		}

		// Prepare and batch delete all the redundant keys.
		$objects_to_delete = array();

		foreach ( $keys_to_remove as $source_id => $objects ) {
			foreach ( $objects as $idx => $object ) {
				// If key was not moved, don't delete it.
				if ( in_array( $object, $keys[ $source_id ] ) && in_array( $object, $new_keys[ $source_id ] ) ) {
					unset( $keys_to_remove[ $source_id ][ $idx ] );
					continue;
				}

				$objects_to_delete[] = array( 'Key' => $object );
			}
		}

		if ( ! empty( $objects_to_delete ) ) {
			try {
				$client->delete_objects( array(
					'Bucket' => $bucket,
					'Delete' => array(
						'Objects' => $objects_to_delete,
					),
				) );
			} catch ( Exception $e ) {
				AS3CF_Error::log( $e->getMessage() );
			}
		}

		$this->update_item_provider_info( $keys, $new_keys, $keys_to_remove, $items, $source_type );
	}

	/**
	 * Handle failed keys.
	 *
	 * @param array  $keys     id => ['path1', 'path2', ...]
	 * @param array  $failures [] => ['Key', 'Message']
	 * @param int    $blog_id
	 * @param string $source_type
	 * @param array  $new_keys id => ['path1', 'path2', ...]
	 *
	 * @return array Keys that can be removed, old and new (roll-back)
	 */
	protected function handle_failed_keys( $keys, $failures, $blog_id, $source_type, $new_keys ) {
		foreach ( $failures as $failure ) {
			foreach ( $new_keys as $source_id => $source_keys ) {
				$key_id = array_search( $failure['Key'], $source_keys );

				if ( false !== $key_id ) {
					$error_msg = sprintf(
						__( 'Error moving %1$s to %2$s for item %3$d: %4$s', 'amazon-s3-and-cloudfront' ),
						$keys[ $source_id ][ $key_id ],
						$failure['Key'],
						$source_id,
						$failure['Message']
					);

					$this->record_error( $blog_id, $source_type, $source_id, $error_msg );

					// Instead of deleting old keys for item, delete new ones (roll-back).
					$keys[ $source_id ] = $new_keys[ $source_id ];

					// Prevent further errors being shown for aborted item.
					unset( $new_keys[ $source_id ] );

					break;
				}
			}
		}

		return $keys;
	}

	/**
	 * Update item provider info.
	 *
	 * @param array  $keys         id => ['path1', 'path2', ...]
	 * @param array  $new_keys     id => ['path1', 'path2', ...]
	 * @param array  $removed_keys id => ['path1', 'path2', ...]
	 * @param array  $items        id => ['prefix' => 'new/path/prefix', 'private_prefix' => 'private']
	 * @param string $source_type
	 */
	protected function update_item_provider_info( $keys, $new_keys, $removed_keys, $items, $source_type ) {
		// There absolutely should be old keys, new keys, some removed/moved, and item prefix data.
		if ( empty( $keys ) || empty( $new_keys ) || empty( $removed_keys ) || empty( $items ) ) {
			return;
		}

		/** @var Item $class */
		$class = $this->as3cf->get_source_type_class( $source_type );

		foreach ( $keys as $source_id => $source_keys ) {
			if ( empty( $items[ $source_id ] ) || empty( $new_keys[ $source_id ] ) || empty( $removed_keys[ $source_id ] ) ) {
				continue;
			}

			// As long as none of the new keys have been removed (roll-back),
			// then we're all good to update the primary path and private prefix.
			if ( ! empty( array_intersect( $new_keys[ $source_id ], $removed_keys[ $source_id ] ) ) ) {
				continue;
			}

			$as3cf_item = $class::get_by_source_id( $source_id );

			$extra_info                   = $as3cf_item->extra_info();
			$extra_info['private_prefix'] = $items[ $source_id ]['private_prefix'];

			$as3cf_item->set_extra_info( $extra_info );
			$as3cf_item->update_path_prefix( $items[ $source_id ]['prefix'] );
			$as3cf_item->save();
		}
	}

	/**
	 * Get complete notice message.
	 *
	 * @return string
	 */
	protected function get_complete_message() {
		return __( 'Finished moving media files to new paths.', 'amazon-s3-and-cloudfront' );
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
