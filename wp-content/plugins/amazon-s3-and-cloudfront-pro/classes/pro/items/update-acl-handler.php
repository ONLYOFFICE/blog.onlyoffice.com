<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Items;

use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Item_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Manifest;
use Exception;
use WP_Error;

class Update_Acl_Handler extends Item_Handler {
	/**
	 * @var string
	 */
	protected static $item_handler_key = 'update-acl';

	/**
	 * The default options that should be used if none supplied.
	 *
	 * @return array
	 */
	public static function default_options() {
		return array(
			'object_keys' => array( Item::primary_object_key() ),
			'set_private' => true,
		);
	}

	/**
	 * Prepare
	 *
	 * @param Item  $as3cf_item
	 * @param array $options
	 *
	 * @return Manifest
	 */
	protected function pre_handle( Item $as3cf_item, array $options ) {
		$manifest = new Manifest();

		foreach ( $options['object_keys'] as $object_key ) {
			$manifest->objects[] = array(
				'object_key'  => $object_key,
				'set_private' => $options['set_private'],
			);
		}

		return $manifest;
	}

	/**
	 * Perform the acl update
	 *
	 * @param Item     $as3cf_item
	 * @param Manifest $manifest
	 * @param array    $options
	 *
	 * @return bool|WP_Error
	 */
	protected function handle_item( Item $as3cf_item, Manifest $manifest, array $options ) {
		foreach ( $manifest->objects as $object_to_update ) {
			if ( $as3cf_item->is_private( $object_to_update['object_key'] ) === $object_to_update['set_private'] ) {
				return true;
			}

			$tried  = false;
			$region = empty( $as3cf_item->region() ) ? false : $as3cf_item->region();
			$acl    = $object_to_update['set_private'] ? $this->as3cf->get_storage_provider()->get_private_acl() : $this->as3cf->get_storage_provider()->get_default_acl();

			$previous_state = clone $as3cf_item;
			$as3cf_item->set_is_private( $object_to_update['set_private'], $object_to_update['object_key'] );

			// Only set ACL if allowed.
			if ( ! empty( $acl ) && $this->as3cf->use_acl_for_intermediate_size( 0, $object_to_update['object_key'], $as3cf_item->bucket(), $previous_state ) ) {
				$tried = true;

				$args = array(
					'Bucket' => $as3cf_item->bucket(),
					'Key'    => $previous_state->provider_key( $object_to_update['object_key'] ),
					'ACL'    => $acl,
				);

				try {
					$provider_client = $this->as3cf->get_provider_client( $region, true );
					$provider_client->update_object_acl( $args );
					$as3cf_item->save();
				} catch ( Exception $e ) {
					$error_msg = 'Error setting ACL to "' . $acl . '" for ' . $previous_state->provider_key( $object_to_update['object_key'] ) . ': ' . $e->getMessage();

					return $this->return_handler_error( $error_msg );
				}
			}

			// If signed urls enabled then may need to move object, which is a copy and delete.
			if ( $this->as3cf->private_prefix_enabled() ) {
				$tried = true;

				$args = array(
					'Bucket'     => $as3cf_item->bucket(),
					'Key'        => $as3cf_item->provider_key( $object_to_update['object_key'] ),
					'CopySource' => urlencode( "{$as3cf_item->bucket()}/" . $previous_state->provider_key( $object_to_update['object_key'] ) ),
				);

				$items[] = $args;

				try {
					$provider_client = $this->as3cf->get_provider_client( $region, true );
					$failures        = $provider_client->copy_objects( $items );

					if ( empty( $failures ) ) {
						$provider_client->delete_object( array(
							'Bucket' => $as3cf_item->bucket(),
							'Key'    => $previous_state->provider_key( $object_to_update['object_key'] ),
						) );
					} else {
						$failure = array_shift( $failures );

						$error_msg = sprintf(
							__( 'Error moving %1$s to %2$s for %5$s %3$d: %4$s', 'amazon-s3-and-cloudfront' ),
							$previous_state->provider_key( $object_to_update['object_key'] ),
							$failure['Key'],
							$as3cf_item->source_id(),
							$failure['Message'],
							$this->as3cf->get_source_type_name( $as3cf_item->source_type() )
						);

						return $this->return_handler_error( $error_msg );
					}
					$as3cf_item->save();
				} catch ( Exception $e ) {
					$error_msg = sprintf(
						__( 'Error updating access for %1$s: %2$s', 'amazon-s3-and-cloudfront' ),
						$previous_state->path( $object_to_update['object_key'] ),
						$e->getMessage()
					);

					return $this->return_handler_error( $error_msg );
				}
			}

			if ( ! $tried ) {
				$error_msg = __( 'Error updating item access, neither ACL updating for bucket or Private Path handling enabled.', 'amazon-s3-and-cloudfront' );

				return $this->return_handler_error( $error_msg );
			}
		}

		return true;
	}

	/**
	 * Perform post handle tasks.
	 *
	 * @param Item     $as3cf_item
	 * @param Manifest $manifest
	 * @param array    $options
	 *
	 * @return bool
	 */
	protected function post_handle( Item $as3cf_item, Manifest $manifest, array $options ) {
		return true;
	}
}