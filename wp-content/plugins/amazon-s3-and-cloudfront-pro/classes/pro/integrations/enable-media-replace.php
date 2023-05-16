<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use DeliciousBrains\WP_Offload_Media\Integrations\Media_Library;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Remove_Provider_Handler;
use Exception;

class Enable_Media_Replace extends Integration {
	/**
	 * @var Media_Library
	 */
	private $media_library;

	/**
	 * @var bool
	 */
	private $wait_for_generate_attachment_metadata = false;

	/**
	 * @var string
	 */
	private $downloaded_original;

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		if ( class_exists( 'EnableMediaReplace\EnableMediaReplacePlugin' ) || function_exists( 'enable_media_replace_init' ) ) {
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
		// Make sure EMR allows OME to filter get_attached_file.
		add_filter( 'emr_unfiltered_get_attached_file', '__return_false' );

		// Download the files and return their path so EMR doesn't get tripped up.
		add_filter( 'as3cf_get_attached_file', array( $this, 'download_file' ), 10, 4 );

		// Although EMR uses wp_unique_filename, it discards that potentially new filename for plain replace, but does then use the following filter.
		add_filter( 'emr_unique_filename', array( $this, 'ensure_unique_filename' ), 10, 3 );

		// Remove objects before offload happens, but don't re-offload just yet.
		add_filter( 'as3cf_update_attached_file', array( $this, 'remove_existing_provider_files_during_replace' ), 10, 2 );

		if ( $this->is_replacing_media() ) {
			$this->wait_for_generate_attachment_metadata = true;

			// Let the media library integration know it should wait for all attachment metadata.
			add_filter( 'as3cf_wait_for_generate_attachment_metadata', array( $this, 'wait_for_generate_attachment_metadata' ) );

			// Wait for WordPress core to tell us it has finished generating thumbnails.
			add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_attachment_metadata_done' ) );

			// Add our potentially downloaded primary file to the list files to remove.
			add_filter( 'as3cf_remove_local_files', array( $this, 'filter_remove_local_files' ) );
		}
	}

	/**
	 * Are we waiting for the wp_generate_attachment_metadata filter to fire?
	 *
	 * @handles as3cf_wait_for_generate_attachment_metadata
	 *
	 * @param bool $wait
	 *
	 * @return bool
	 */
	public function wait_for_generate_attachment_metadata( $wait ) {
		if ( $this->wait_for_generate_attachment_metadata ) {
			return true;
		}

		return $wait;
	}

	/**
	 * Update internal state for waiting for attachment_metadata.
	 *
	 * @handles wp_generate_attachment_metadata
	 *
	 * @param array $metadata
	 *
	 * @return array
	 */
	public function generate_attachment_metadata_done( $metadata ) {
		$this->wait_for_generate_attachment_metadata = false;

		return $metadata;
	}

	/**
	 * If we've downloaded an existing primary file from the provider we add it to the
	 * files_to_remove array when the Remove_Local handler runs.
	 *
	 * @handles as3cf_remove_local_files
	 *
	 * @param array $files_to_remove
	 *
	 * @return array
	 */
	public function filter_remove_local_files( $files_to_remove ) {
		if ( ! empty( $this->downloaded_original ) && file_exists( $this->downloaded_original ) && ! in_array( $this->downloaded_original, $files_to_remove ) ) {
			$files_to_remove[] = $this->downloaded_original;
		}

		return $files_to_remove;
	}

	/**
	 * Allow the Enable Media Replace plugin to copy the provider file back to the local
	 * server when the file is missing on the server via get_attached_file().
	 *
	 * @param string             $url
	 * @param string             $file
	 * @param int                $attachment_id
	 * @param Media_Library_Item $as3cf_item
	 *
	 * @return string
	 */
	public function download_file( $url, $file, $attachment_id, Media_Library_Item $as3cf_item ) {
		$this->downloaded_original = $this->as3cf->plugin_compat->copy_image_to_server_on_action( 'media_replace_upload', false, $url, $file, $as3cf_item );

		return $this->downloaded_original;
	}

	/**
	 * EMR deletes the original files before replace, then updates metadata etc.
	 * So we should remove associated offloaded files too, and let normal (re)offload happen afterwards.
	 *
	 * @param string $file
	 * @param int    $attachment_id
	 *
	 * @return string
	 * @throws Exception
	 */
	public function remove_existing_provider_files_during_replace( $file, $attachment_id ) {
		if ( ! $this->is_replacing_media() ) {
			return $file;
		}

		if ( ! $this->as3cf->is_plugin_setup( true ) ) {
			return $file;
		}

		$as3cf_item = Media_Library_Item::get_by_source_id( $attachment_id );

		if ( ! empty( $as3cf_item ) ) {
			$remove_provider = $this->as3cf->get_item_handler( Remove_Provider_Handler::get_item_handler_key_name() );
			$remove_provider->handle( $as3cf_item, array( 'verify_exists_on_local' => false ) );

			// By deleting the item here, a new one will be created by when EMR generates the thumbnails and our ML integration
			// picks it up. Ensuring that the object versioning string and object list are generated fresh.
			$as3cf_item->delete();
		}

		return $file;
	}

	/**
	 * Are we doing a media replacement?
	 *
	 * @return bool
	 */
	public function is_replacing_media() {
		$action = filter_input( INPUT_GET, 'action' );

		if ( empty( $action ) ) {
			return false;
		}

		return ( 'media_replace_upload' === sanitize_key( $action ) );
	}

	/**
	 * Ensure the generated filename for an image replaced with a new image is unique.
	 *
	 * @param string $filename File name that should be unique.
	 * @param string $path     Absolute path to where the file will go.
	 * @param int    $id       Attachment ID.
	 *
	 * @return string
	 */
	public function ensure_unique_filename( $filename, $path, $id ) {
		// Get extension.
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		$ext = $ext ? ".$ext" : '';

		return $this->media_library->filter_unique_filename( $filename, $ext, $path, $id );
	}
}
