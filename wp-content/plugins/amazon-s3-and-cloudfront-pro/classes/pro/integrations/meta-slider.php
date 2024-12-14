<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use DeliciousBrains\WP_Offload_Media\Integrations\Media_Library;
use DeliciousBrains\WP_Offload_Media\Items\Download_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Items\Upload_Handler;
use Exception;

class Meta_Slider extends Integration {
	/**
	 * @var Media_Library
	 */
	private $media_library;

	/**
	 * Keep track of get_metadata recursion level
	 *
	 * @var int
	 */
	private $get_postmeta_recursion_level = 0;

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		if ( class_exists( 'MetaSliderPlugin' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Init integration.
	 */
	public function init() {
		$this->media_library = $this->as3cf->get_integration_manager()->get_integration( 'mlib' );
	}

	/**
	 * @inheritDoc
	 */
	public function setup() {
		add_filter( 'metaslider_attachment_url', array( $this, 'metaslider_attachment_url' ), 10, 2 );
		add_filter( 'sanitize_post_meta_amazonS3_info', array( $this, 'layer_slide_sanitize_post_meta' ), 10, 3 );
		add_filter( 'as3cf_pre_update_attachment_metadata', array( $this, 'layer_slide_abort_upload' ), 10, 4 );
		add_filter( 'as3cf_remove_attachment_paths', array( $this, 'layer_slide_remove_attachment_paths' ), 10, 4 );
		add_action( 'add_post_meta', array( $this, 'add_post_meta' ), 10, 3 );
		add_action( 'update_post_meta', array( $this, 'update_post_meta' ), 10, 4 );

		// Maybe download primary image.
		add_filter( 'as3cf_get_attached_file', array( $this, 'download_for_resize' ), 10, 4 );

		// Filter HTML in layer sliders when they are saved and fetched.
		add_filter( 'sanitize_post_meta_ml-slider_html', array( $this, 'sanitize_layer_slider_html' ) );
		add_filter( 'get_post_metadata', array( $this, 'filter_get_post_metadata' ), 10, 4 );
	}

	/**
	 * Use the Provider URL for a Meta Slider slide image.
	 *
	 * @handles metaslider_attachment_url
	 *
	 * @param string $url
	 * @param int    $slide_id
	 *
	 * @return string
	 */
	public function metaslider_attachment_url( $url, $slide_id ) {
		$provider_url = $this->media_library->wp_get_attachment_url( $url, $slide_id );

		if ( ! is_wp_error( $provider_url ) && false !== $provider_url ) {
			return $provider_url;
		}

		return $url;
	}

	/**
	 * Layer slide sanitize post meta.
	 *
	 * This fixes issues with 'Layer Slides', which uses `get_post_custom` to retrieve
	 * attachment meta, but does not unserialize the data. This results in the `amazonS3_info`
	 * key being double serialized when inserted into the database.
	 *
	 * @handles sanitize_post_meta_amazonS3_info
	 *
	 * @param mixed  $meta_value
	 * @param string $meta_key
	 * @param string $object_type
	 *
	 * @return mixed
	 *
	 * Note: Legacy filter, kept for migration purposes.
	 */
	public function layer_slide_sanitize_post_meta( $meta_value, $meta_key, $object_type ) {
		if ( ! $this->is_layer_slide() ) {
			return $meta_value;
		}

		return maybe_unserialize( $meta_value );
	}

	/**
	 * Layer slide abort upload.
	 *
	 * 'Layer Slide' duplicates an attachment in the Media Library, but uses the same
	 * file as the original. This prevents us trying to upload a new version to the bucket.
	 *
	 * @handles as3cf_pre_update_attachment_metadata
	 *
	 * @param bool                    $pre
	 * @param mixed                   $data
	 * @param int                     $post_id
	 * @param Media_Library_Item|null $as3cf_item
	 *
	 * @return bool
	 */
	public function layer_slide_abort_upload( $pre, $data, $post_id, Media_Library_Item $as3cf_item = null ) {
		if ( $this->is_layer_slide() && empty( $as3cf_item ) ) {
			$original_id = filter_input( INPUT_POST, 'slide_id' );
			if ( empty( $original_id ) ) {
				return $pre;
			}

			$original_item = Media_Library_Item::get_by_source_id( $original_id );
			if ( empty( $original_item ) ) {
				return $pre;
			}

			$as3cf_item = Media_Library_Item::create_from_source_id( $post_id );
			if ( empty( $as3cf_item ) ) {
				return $pre;
			}

			$as3cf_item->set_path( $original_item->path() );
			$as3cf_item->set_original_path( $original_item->original_path() );
			$as3cf_item->set_extra_info( $original_item->extra_info() );
			$as3cf_item->save();

			return true;
		}

		return $pre;
	}

	/**
	 * Layer slide remove attachment paths.
	 *
	 * Because 'Layer Slide' duplicates an attachment in the Media Library, but uses the same
	 * file as the original we don't want to remove them from the bucket. Only the backup sizes
	 * should be removed.
	 *
	 * @handles as3cf_remove_attachment_paths
	 *
	 * @param array              $paths
	 * @param int                $post_id
	 * @param Media_Library_Item $item
	 * @param bool               $remove_backup_sizes
	 *
	 * @return array
	 */
	public function layer_slide_remove_attachment_paths( $paths, $post_id, Media_Library_Item $item, $remove_backup_sizes ) {
		$slider = get_post_meta( $post_id, 'ml-slider_type', true );

		if ( 'html_overlay' !== $slider ) {
			// Not a layer slide, return.
			return $paths;
		}

		$meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );

		unset( $paths[ Media_Library_Item::primary_object_key() ] );
		if ( isset( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size => $details ) {
				unset( $paths[ $size ] );
			}
		}

		return $paths;
	}

	/**
	 * Is layer slide.
	 *
	 * @return bool
	 */
	private function is_layer_slide() {
		if ( 'create_html_overlay_slide' === filter_input( INPUT_POST, 'action' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add post meta
	 *
	 * @handles add_post_meta
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $_meta_value
	 *
	 * @throws Exception
	 */
	public function add_post_meta( $object_id, $meta_key, $_meta_value ) {
		$this->maybe_upload_attachment_backup_sizes( $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Update post meta
	 *
	 * @handles update_post_meta
	 *
	 * @param int    $meta_id
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $_meta_value
	 *
	 * @throws Exception
	 */
	public function update_post_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
		$this->maybe_upload_attachment_backup_sizes( $object_id, $meta_key, $_meta_value );
	}

	/**
	 * Rewrites remote URLs to local when Meta Slider saves HTML layer slides.
	 *
	 * @handles sanitize_post_meta_ml-slider_html
	 *
	 * @param string $meta_value
	 *
	 * @return string
	 */
	public function sanitize_layer_slider_html( $meta_value ) {
		return $this->as3cf->filter_provider->filter_post( $meta_value );
	}

	/**
	 * Rewrites remote URLs to local when Meta Slider gets HTML layer slides.
	 *
	 * @handles get_post_metadata
	 *
	 * @param mixed  $check
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return string
	 */
	public function filter_get_post_metadata( $check, $object_id, $meta_key, $meta_value ) {
		// Exit early if this is not our key to process.
		if ( 'ml-slider_html' !== $meta_key ) {
			return $check;
		}

		// We're calling get_metadata recursively and need to make sure
		// we never nest deeper than one level.
		if ( 0 === $this->get_postmeta_recursion_level ) {
			$this->get_postmeta_recursion_level++;
			$new_meta_value = get_metadata( 'post', $object_id, $meta_key, true );
			$new_meta_value = $this->as3cf->filter_local->filter_post( $new_meta_value );

			// Reset recursion.
			$this->get_postmeta_recursion_level = 0;

			return $new_meta_value;
		}

		return $check;
	}

	/**
	 * Allow meta slider to resize images that have been removed from local
	 *
	 * @handles as3cf_get_attached_file
	 *
	 * @param string             $url
	 * @param string             $file
	 * @param int                $attachment_id
	 * @param Media_Library_Item $as3cf_item
	 *
	 * @return string
	 */
	public function download_for_resize( $url, $file, $attachment_id, Media_Library_Item $as3cf_item ) {
		$action = filter_input( INPUT_POST, 'action' );
		if ( ! in_array( $action, array( 'resize_image_slide', 'create_html_overlay_slide' ) ) ) {
			return $url;
		}

		$download_handler = $this->as3cf->get_item_handler( Download_Handler::get_item_handler_key_name() );
		$result           = $download_handler->handle( $as3cf_item, array( 'full_source_paths' => array( $file ) ) );

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return $url;
		}

		return $file;
	}

	/**
	 * Maybe upload attachment backup sizes
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $data
	 *
	 * @throws Exception
	 */
	private function maybe_upload_attachment_backup_sizes( $object_id, $meta_key, $data ) {
		if ( '_wp_attachment_backup_sizes' !== $meta_key ) {
			return;
		}

		if ( 'resize_image_slide' !== filter_input( INPUT_POST, 'action' ) && ! $this->is_layer_slide() ) {
			return;
		}

		if ( ! $this->as3cf->is_plugin_setup( true ) ) {
			return;
		}

		$item = Media_Library_Item::get_by_source_id( $object_id );

		if ( ! $item && ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
			// Abort if not already offloaded to provider and the copy setting is off.
			return;
		}

		$this->upload_attachment_backup_sizes( $object_id, $item, $data );
	}

	/**
	 * Upload attachment backup sizes
	 *
	 * @param int                $object_id
	 * @param Media_Library_Item $as3cf_item
	 * @param mixed              $data
	 *
	 * @throws Exception
	 */
	private function upload_attachment_backup_sizes( $object_id, Media_Library_Item $as3cf_item, $data ) {
		foreach ( $data as $key => $file ) {
			if ( ! isset( $file['path'] ) ) {
				continue;
			}

			$objects = $as3cf_item->objects();

			if ( ! empty( $objects[ $key ] ) ) {
				continue;
			}

			$options = array(
				'offloaded_files' => $as3cf_item->offloaded_files(),
			);

			$objects[ $key ] = array(
				'source_file' => wp_basename( $file['path'] ),
				'is_private'  => false,
			);

			$as3cf_item->set_objects( $objects );
			$upload_handler = $this->as3cf->get_item_handler( Upload_Handler::get_item_handler_key_name() );
			$upload_handler->handle( $as3cf_item, $options );
		}
	}
}
