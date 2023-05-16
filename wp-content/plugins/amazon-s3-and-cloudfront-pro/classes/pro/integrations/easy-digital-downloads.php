<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations;

use Amazon_S3_And_CloudFront_Pro;
use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Update_Acl_Handler;
use Exception;

class Easy_Digital_Downloads extends Integration {

	/**
	 * @var Amazon_S3_And_CloudFront_Pro
	 */
	protected $as3cf;

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
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
		// Set download method to redirect
		add_filter( 'edd_file_download_method', array( $this, 'set_download_method' ) );
		// Disable using symlinks for download.
		add_filter( 'edd_symlink_file_downloads', array( $this, 'disable_symlink_file_downloads' ) );
		// Hook into edd_requested_file to swap in the S3 secure URL
		add_filter( 'edd_requested_file', array( $this, 'get_download_url' ), 10, 3 );
		// Hook into the save download files metabox to apply the private ACL
		add_filter( 'edd_metabox_save_edd_download_files', array( $this, 'make_edd_files_private_on_provider' ), 11 );
	}

	/**
	 * Set download method
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	public function set_download_method( $method ) {
		return 'redirect';
	}

	/**
	 * Disable symlink file downloads
	 *
	 * @param bool $use_symlink
	 *
	 * @return bool
	 */
	public function disable_symlink_file_downloads( $use_symlink ) {
		return false;
	}

	/**
	 * Uses the secure S3 url for downloads of a file
	 *
	 * @param string $file
	 * @param array  $download_files
	 * @param string $file_key
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function get_download_url( $file, $download_files, $file_key ) {
		global $edd_options;

		$file_data = $download_files[ $file_key ];
		$file_name = $file_data['file'];
		$post_id   = $file_data['attachment_id'];
		$expires   = apply_filters( 'as3cf_edd_download_expires', 5 );
		$headers   = apply_filters( 'as3cf_edd_download_headers', array(
			'ResponseContentDisposition' => 'attachment',
		), $file_data );

		// Standard Offloaded Media Library Item.
		$as3cf_item = Media_Library_Item::get_by_source_id( $post_id );

		if ( $as3cf_item && ! is_wp_error( $as3cf_item ) ) {
			return $as3cf_item->get_provider_url( null, $expires, $headers );
		}

		// Official EDD S3 addon upload - path should not start with '/', 'http', 'https' or 'ftp' or contain AWSAccessKeyId
		$url = parse_url( $file_name );

		if ( ( '/' !== $file_name[0] && false === isset( $url['scheme'] ) ) || false !== ( strpos( $file_name, 'AWSAccessKeyId' ) ) ) {
			$bucket     = ( isset( $edd_options['edd_amazon_s3_bucket'] ) ) ? trim( $edd_options['edd_amazon_s3_bucket'] ) : $this->as3cf->get_setting( 'bucket' );
			$expires    = time() + $expires;
			$secure_url = $this->as3cf->get_provider_client()->get_object_url( $bucket, $file_name, $expires, $headers );

			return $secure_url;
		}

		// None S3 upload
		return $file;
	}

	/**
	 * Apply ACL to files uploaded outside of EDD on save of EDD download files
	 *
	 * @param array $files
	 *
	 * @return mixed
	 */
	public function make_edd_files_private_on_provider( $files ) {
		global $post;

		// get existing files attached to download
		$old_files          = edd_get_download_files( $post->ID );
		$old_attachment_ids = wp_list_pluck( $old_files, 'attachment_id' );
		$new_attachment_ids = array();

		/** @var Update_Acl_Handler $acl_handler */
		$acl_handler = $this->as3cf->get_item_handler( Update_Acl_Handler::get_item_handler_key_name() );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				$new_attachment_ids[] = $file['attachment_id'];

				$as3cf_item = Media_Library_Item::get_by_source_id( $file['attachment_id'] );

				if ( ! $as3cf_item ) {
					// not offloaded, ignore.
					continue;
				}

				if ( $as3cf_item->is_private() ) {
					// already private
					continue;
				}

				if ( $this->as3cf->is_pro_plugin_setup( true ) ) {

					$options = array(
						'object_keys' => array( null ),
						'set_private' => true,
					);
					$result  = $acl_handler->handle( $as3cf_item, $options );

					if ( true === $result ) {
						$this->as3cf->make_acl_admin_notice( $as3cf_item );
					}
				}
			}
		}

		// determine which attachments have been removed and maybe set to public
		$removed_attachment_ids = array_diff( $old_attachment_ids, $new_attachment_ids );
		$this->maybe_make_removed_edd_files_public( $removed_attachment_ids, $post->ID );

		return $files;
	}

	/**
	 * Remove public ACL from attachments removed from a download
	 * as long as they are not attached to any other downloads
	 *
	 * @param array   $attachment_ids
	 * @param integer $download_id
	 */
	function maybe_make_removed_edd_files_public( $attachment_ids, $download_id ) {
		global $wpdb;

		/** @var Update_Acl_Handler $acl_handler */
		$acl_handler = $this->as3cf->get_item_handler( Update_Acl_Handler::get_item_handler_key_name() );

		foreach ( $attachment_ids as $id ) {
			$as3cf_item = Media_Library_Item::get_by_source_id( $id );

			if ( ! $as3cf_item ) {
				// Not offloaded, ignore.
				continue;
			}

			if ( ! $as3cf_item->is_private() ) {
				// already public
				continue;
			}

			$length = strlen( $id );

			// check the attachment isn't used by other downloads
			$sql = "
				SELECT COUNT(*)
				FROM `{$wpdb->prefix}postmeta`
				WHERE `{$wpdb->prefix}postmeta`.`meta_key` = 'edd_download_files'
				AND `{$wpdb->prefix}postmeta`.`post_id` != {$download_id}
				AND `{$wpdb->prefix}postmeta`.`meta_value` LIKE '%s:13:\"attachment_id\";s:{$length}:\"{$id}\"%'
			";

			if ( $wpdb->get_var( $sql ) > 0 ) {
				// used for another download, ignore
				continue;
			}

			// set acl to public
			$options = array(
				'object_keys' => array( null ),
				'set_private' => false,
			);
			$result  = $acl_handler->handle( $as3cf_item, $options );

			if ( true === $result ) {
				$this->as3cf->make_acl_admin_notice( $as3cf_item );
			}
		}
	}
}
