<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Integrations\Integration;

class Wpml extends Integration {

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		if ( class_exists( 'SitePress' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Init integration.
	 */
	public function init() {
		// Nothing to do.
	}

	/**
	 * @inheritDoc
	 */
	public function setup() {
		add_action( 'wpml_media_create_duplicate_attachment', array( $this, 'duplicate_offloaded_item' ), 10, 2 );
	}

	/**
	 * Duplicate original item's offload data for new duplicate Media Library item.
	 *
	 * WPML duplicates postmeta in reverse order which unfortunately means we can't catch the new attachment and offload it.
	 * But, WPML does fire an action after each item is duplicated, so we can just duplicate our data too.
	 *
	 * @param int $attachment_id
	 * @param int $new_attachment_id
	 */
	public function duplicate_offloaded_item( $attachment_id, $new_attachment_id ) {
		$old_item = Media_Library_Item::get_by_source_id( $attachment_id );

		if ( $old_item ) {
			$as3cf_item = Media_Library_Item::get_by_source_id( $new_attachment_id );

			if ( ! $as3cf_item ) {
				$as3cf_item = new Media_Library_Item(
					$old_item->provider(),
					$old_item->region(),
					$old_item->bucket(),
					$old_item->path(),
					$old_item->is_private(),
					$new_attachment_id,
					$old_item->source_path(),
					wp_basename( $old_item->original_source_path() ),
					$old_item->extra_info()
				);

				$as3cf_item->save();
				$as3cf_item->duplicate_filesize_total( $attachment_id );
			}
		}
	}
}
