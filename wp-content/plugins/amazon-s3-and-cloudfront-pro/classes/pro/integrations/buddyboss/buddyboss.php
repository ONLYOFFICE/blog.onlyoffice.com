<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss;

use AS3CF_Error;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Integrations\Integration;
use DeliciousBrains\WP_Offload_Media\Items\Download_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Upload_Handler;
use DeliciousBrains\WP_Offload_Media\Pro\Items\Remove_Provider_Handler;
use Exception;

class BuddyBoss extends Integration {
	/**
	 * Our item types
	 *
	 * @var object[]
	 */
	private $source_types;

	/**
	 * Are we inside a crop operation?
	 *
	 * @var bool
	 */
	private $in_crop = false;

	/**
	 * Did we handle a crop operation?
	 *
	 * @var bool
	 */
	private $did_crop = false;

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {
		global $buddyboss_platform_plugin_file;

		if ( class_exists( 'BuddyPress' ) && is_string( $buddyboss_platform_plugin_file ) && ! is_multisite() ) {
			return true;
		}

		return false;
	}

	/**
	 * Init integration.
	 */
	public function init() {
		$this->source_types = array(
			'bboss-user-avatar'  => array(
				'class' => BBoss_Item::get_item_class( 'user', 'avatar' ),
			),
			'bboss-user-cover'   => array(
				'class' => BBoss_Item::get_item_class( 'user', 'cover' ),
			),
			'bboss-group-avatar' => array(
				'class' => BBoss_Item::get_item_class( 'group', 'avatar' ),
			),
			'bboss-group-cover'  => array(
				'class' => BBoss_Item::get_item_class( 'group', 'cover' ),
			),
		);

		// Register our item source types with the global as3cf object.
		foreach ( $this->source_types as $key => $source_type ) {
			$this->as3cf->register_source_type( $key, $source_type['class'] );
		}

		// Register our item summary types with the global as3cf object.
		$this->as3cf->register_summary_type( BBoss_Item::summary_type(), BBoss_Item::class );
	}

	/**
	 * @inheritDoc
	 */
	public function setup() {
		// URL Rewriting.
		add_filter( 'bp_core_fetch_avatar_url_check', array( $this, 'fetch_avatar' ), 10, 2 );
		add_filter( 'bp_core_fetch_gravatar_url_check', array( $this, 'fetch_default_avatar' ), 99, 2 );
		add_filter( 'bb_get_default_custom_upload_profile_avatar', array( $this, 'filter_bb_get_default_custom_upload_profile_avatar' ), 10, 2 );
		add_filter( 'bb_get_default_custom_upload_group_avatar', array( $this, 'filter_bb_get_default_custom_upload_group_avatar' ), 10, 2 );
		add_filter( 'bp_attachments_pre_get_attachment', array( $this, 'fetch_cover' ), 10, 2 );
		add_filter( 'bb_get_default_custom_upload_profile_cover', array( $this, 'filter_bb_get_default_custom_upload_profile_cover' ), 10 );
		add_filter( 'bb_get_default_custom_upload_group_cover', array( $this, 'filter_bb_get_default_custom_upload_group_cover' ), 10 );

		// Storage handling.
		add_action( 'bp_core_pre_avatar_handle_crop', array( $this, 'filter_bp_core_pre_avatar_handle_crop' ), 10, 2 );
		add_action( 'xprofile_avatar_uploaded', array( $this, 'avatar_uploaded' ), 10, 3 );
		add_action( 'groups_avatar_uploaded', array( $this, 'avatar_uploaded' ), 10, 3 );
		add_action( 'xprofile_cover_image_uploaded', array( $this, 'user_cover_uploaded' ), 10, 1 );
		add_action( 'groups_cover_image_uploaded', array( $this, 'groups_cover_uploaded' ), 10, 1 );
		add_action( 'bp_core_delete_existing_avatar', array( $this, 'delete_existing_avatar' ), 10, 1 );
		add_action( 'xprofile_cover_image_deleted', array( $this, 'delete_existing_user_cover' ), 10, 1 );
		add_action( 'groups_cover_image_deleted', array( $this, 'delete_existing_group_cover' ), 10, 1 );
		add_action( 'deleted_user', array( $this, 'handle_deleted_user' ), 10, 1 );
		add_action( 'groups_delete_group', array( $this, 'groups_delete_group' ), 10, 1 );
		add_filter( 'bp_attachments_pre_delete_file', array( $this, 'bp_attachments_pre_delete_file' ), 10, 2 );

		// Internal filters.
		add_filter( 'as3cf_remove_size_from_filename', array( $this, 'remove_size_from_filename' ), 10, 1 );
		add_filter( 'as3cf_get_size_string_from_url_for_item_source', array( $this, 'get_size_string_from_url_for_item_source' ), 10, 3 );
		add_filter( 'as3cf_get_provider_url_for_item_source', array( $this, 'filter_get_provider_url_for_item_source' ), 10, 3 );
		add_filter( 'as3cf_get_local_url_for_item_source', array( $this, 'filter_get_local_url_for_item_source' ), 10, 3 );
		add_filter( 'as3cf_strip_image_edit_suffix_and_extension', array( $this, 'filter_strip_image_edit_suffix_and_extension' ), 10, 2 );
	}

	/**
	 * If possible, rewrite local avatar URL to remote, possibly using substitute source.
	 *
	 * @param string   $avatar_url
	 * @param array    $params
	 * @param null|int $source_id Optional override for the source ID, e.g. default = 0.
	 *
	 * @return string
	 */
	private function rewrite_avatar_url( $avatar_url, $params, $source_id = null ) {
		if ( ! $this->as3cf->get_setting( 'serve-from-s3' ) ) {
			return $avatar_url;
		}

		if ( ! isset( $params['item_id'] ) || ! is_numeric( $params['item_id'] ) || empty( $params['object'] ) ) {
			return $avatar_url;
		}

		if ( ! empty( $avatar_url ) && ! $this->as3cf->filter_local->url_needs_replacing( $avatar_url ) ) {
			return $avatar_url;
		}

		if ( ! is_numeric( $source_id ) ) {
			$source_id = $params['item_id'];
		}

		$as3cf_item = BBoss_Item::get_buddy_boss_item( $source_id, $params['object'], 'avatar' );
		if ( false !== $as3cf_item ) {
			$image_type = ! empty( $params['type'] ) ? $params['type'] : 'full';
			$size       = 'thumb' === $image_type ? 'thumb' : Item::primary_object_key();

			$new_url = $as3cf_item->get_provider_url( $size );

			if ( ! empty( $new_url ) && is_string( $new_url ) ) {
				return $new_url;
			}
		}

		return $avatar_url;
	}

	/**
	 * Returns the avatar's remote URL.
	 *
	 * @handles bp_core_fetch_avatar_url_check
	 *
	 * @param string $avatar_url
	 * @param array  $params
	 *
	 * @return string
	 */
	public function fetch_avatar( $avatar_url, $params ) {
		return $this->rewrite_avatar_url( $avatar_url, $params );
	}

	/**
	 * Returns the avatar's remote default URL if gravatar not supplied.
	 *
	 * @handles bp_core_fetch_gravatar_url_check
	 *
	 * @param string $avatar_url
	 * @param array  $params
	 *
	 * @return string
	 */
	public function fetch_default_avatar( $avatar_url, $params ) {
		return $this->rewrite_avatar_url( $avatar_url, $params, 0 );
	}

	/**
	 * Filters to change default custom upload avatar image.
	 *
	 * @handles bb_get_default_custom_upload_profile_avatar
	 *
	 * @param string $custom_upload_profile_avatar Default custom upload avatar URL.
	 * @param string $size                         This parameter specifies whether you'd like the 'full' or 'thumb' avatar.
	 */
	public function filter_bb_get_default_custom_upload_profile_avatar( $custom_upload_profile_avatar, $size ) {
		$params = array(
			'item_id' => 0,
			'object'  => 'user',
			'type'    => $size,
		);

		return $this->rewrite_avatar_url( $custom_upload_profile_avatar, $params );
	}

	/**
	 * Filters to change default custom upload avatar image.
	 *
	 * @handles bb_get_default_custom_upload_group_avatar
	 *
	 * @param string $custom_upload_group_avatar Default custom upload avatar URL.
	 * @param string $size                       This parameter specifies whether you'd like the 'full' or 'thumb' avatar.
	 */
	public function filter_bb_get_default_custom_upload_group_avatar( $custom_upload_group_avatar, $size ) {
		$params = array(
			'item_id' => 0,
			'object'  => 'group',
			'type'    => $size,
		);

		return $this->rewrite_avatar_url( $custom_upload_group_avatar, $params );
	}

	/**
	 * If possible, rewrite local cover URL to remote, possibly using substitute source.
	 *
	 * @param string   $cover_url
	 * @param array    $params
	 * @param null|int $source_id Optional override for the source ID, e.g. default = 0.
	 *
	 * @return string
	 */
	private function rewrite_cover_url( $cover_url, $params, $source_id = null ) {
		if ( ! $this->as3cf->get_setting( 'serve-from-s3' ) ) {
			return $cover_url;
		}

		if ( ! isset( $params['item_id'] ) || ! is_numeric( $params['item_id'] ) || empty( $params['object_dir'] ) ) {
			return $cover_url;
		}

		$object_type = $this->object_type_from_dir( $params['object_dir'] );
		if ( is_null( $object_type ) ) {
			return $cover_url;
		}

		if ( ! empty( $cover_url ) && ! $this->as3cf->filter_local->url_needs_replacing( $cover_url ) ) {
			return $cover_url;
		}

		if ( ! is_numeric( $source_id ) ) {
			$source_id = $params['item_id'];
		}

		$as3cf_item = BBoss_Item::get_buddy_boss_item( $source_id, $object_type, 'cover' );
		if ( false !== $as3cf_item ) {
			$new_url = $as3cf_item->get_provider_url( Item::primary_object_key() );

			if ( ! empty( $new_url ) && is_string( $new_url ) ) {
				// We should not supply remote URL during a delete operation,
				// but the delete process will fail if there isn't a local file to delete.
				if ( isset( $_POST['action'] ) && 'bp_cover_image_delete' === $_POST['action'] ) {
					if ( ! $as3cf_item->exists_locally() ) {
						/** @var Download_Handler $download_handler */
						$download_handler = $this->as3cf->get_item_handler( Download_Handler::get_item_handler_key_name() );
						$download_handler->handle( $as3cf_item );
					}

					return $cover_url;
				}

				return $new_url;
			}
		}

		return $cover_url;
	}

	/**
	 * Returns the cover's remote URL.
	 *
	 * @handles bp_attachments_pre_get_attachment
	 *
	 * @param string $cover_url
	 * @param array  $params
	 *
	 * @return string
	 */
	public function fetch_cover( $cover_url, $params ) {
		return $this->rewrite_cover_url( $cover_url, $params );
	}

	/**
	 * Filters to change default custom upload cover image.
	 *
	 * @handles bb_get_default_custom_upload_profile_cover
	 *
	 * @param string $value Default custom upload profile cover URL.
	 */
	public function filter_bb_get_default_custom_upload_profile_cover( $value ) {
		$params = array(
			'item_id'    => 0,
			'object_dir' => 'members',
		);

		return $this->rewrite_cover_url( $value, $params );
	}

	/**
	 * Filters default custom upload cover image URL.
	 *
	 * @handles bb_get_default_custom_upload_group_cover
	 *
	 * @param string $value Default custom upload group cover URL.
	 */
	public function filter_bb_get_default_custom_upload_group_cover( $value ) {
		$params = array(
			'item_id'    => 0,
			'object_dir' => 'groups',
		);

		return $this->rewrite_cover_url( $value, $params );
	}

	/**
	 * Filters whether or not to handle cropping.
	 *
	 * But we use it to catch a successful crop so we can offload
	 * and later supply the correct remote URL.
	 *
	 * @handles bp_core_pre_avatar_handle_crop
	 *
	 * @param bool  $value Whether or not to crop.
	 * @param array $r     Array of parsed arguments for function.
	 *
	 * @throws Exception
	 */
	public function filter_bp_core_pre_avatar_handle_crop( $value, $r ) {
		if ( ! function_exists( 'bp_core_avatar_handle_crop' ) ) {
			return $value;
		}

		$this->in_crop = ! $this->in_crop;

		if ( $this->in_crop ) {
			if ( bp_core_avatar_handle_crop( $r ) ) {
				$this->avatar_uploaded( $r['item_id'], 'crop', $r );
				$this->did_crop = true;
			}

			// We handled the crop.
			return false;
		}

		// Don't cancel operation when we call it above.
		return $value;
	}

	/**
	 * Handle a newly uploaded avatar
	 *
	 * @handles xprofile_avatar_uploaded
	 * @handles groups_avatar_uploaded
	 *
	 * @param int    $source_id
	 * @param string $avatar_type
	 * @param array  $params
	 *
	 * @throws Exception
	 */
	public function avatar_uploaded( $source_id, $avatar_type, $params ) {
		if ( $this->did_crop ) {
			return;
		}

		if ( ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
			return;
		}

		if ( empty( $params['object'] ) ) {
			return;
		}

		$object_type = $params['object'];
		$image_type  = 'avatar';

		$as3cf_item = BBoss_Item::get_buddy_boss_item( $source_id, $object_type, $image_type );
		if ( false !== $as3cf_item ) {
			$this->delete_existing_avatar( array( 'item_id' => $source_id, 'object' => $object_type ) );
		}

		/** @var BBoss_Item $class */
		$class      = BBoss_Item::get_item_class( $object_type, $image_type );
		$as3cf_item = $class::create_from_source_id( $source_id );

		$upload_handler = $this->as3cf->get_item_handler( Upload_Handler::get_item_handler_key_name() );
		$upload_result  = $upload_handler->handle( $as3cf_item );

		// TODO: Should not be needed ...
		if ( is_wp_error( $upload_result ) ) {
			return;
		}

		// TODO: ... as this should be redundant.
		// TODO: However, when user has offloaded avatar and replaces it,
		// TODO: this save is required as handler returns false.
		// TODO: As there is a delete above, this should not be the case!
		$as3cf_item->save();
	}

	/**
	 * Handle when a new user cover image is uploaded
	 *
	 * @handles xprofile_cover_image_uploaded
	 *
	 * @param int $source_id
	 *
	 * @throws Exception
	 */
	public function user_cover_uploaded( $source_id ) {
		$this->cover_uploaded( $source_id, 'user' );
	}

	/**
	 * Handle when a new group cover image is uploaded
	 *
	 * @handles xprofile_cover_image_uploaded
	 *
	 * @param int $source_id
	 *
	 * @throws Exception
	 */
	public function groups_cover_uploaded( $source_id ) {
		$this->cover_uploaded( $source_id, 'group' );
	}

	/**
	 * Handle a new group or user cover image
	 *
	 * @param int    $source_id
	 * @param string $object_type
	 *
	 * @throws Exception
	 */
	private function cover_uploaded( $source_id, $object_type ) {
		if ( ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
			return;
		}

		$as3cf_item = BBoss_Item::get_buddy_boss_item( $source_id, $object_type, 'cover' );
		if ( false !== $as3cf_item ) {
			$this->delete_existing_cover( $source_id, $object_type );
		}

		/** @var BBoss_Item $class */
		$class      = BBoss_Item::get_item_class( $object_type, 'cover' );
		$as3cf_item = $class::create_from_source_id( $source_id );

		$upload_handler = $this->as3cf->get_item_handler( Upload_Handler::get_item_handler_key_name() );
		$upload_handler->handle( $as3cf_item );
	}

	/**
	 * Removes a user cover image from the remote bucket
	 *
	 * @handles xprofile_cover_image_deleted
	 *
	 * @param int $source_id
	 */
	public function delete_existing_user_cover( $source_id ) {
		$this->delete_existing_cover( $source_id, 'user' );
	}

	/**
	 * Removes a group cover image from the remote bucket
	 *
	 * @handles groups_cover_image_deleted
	 *
	 * @param int $source_id
	 */
	public function delete_existing_group_cover( $source_id ) {
		$this->delete_existing_cover( $source_id, 'group' );
	}

	/**
	 * Removes a cover image from the remote bucket
	 *
	 * @handles bp_core_delete_existing_avatar
	 *
	 * @param int    $source_id
	 * @param string $object_type
	 */
	public function delete_existing_cover( $source_id, $object_type ) {
		/** @var BBoss_Item $as3cf_item */
		$as3cf_item = BBoss_Item::get_buddy_boss_item( $source_id, $object_type, 'cover' );
		if ( ! empty( $as3cf_item ) ) {
			$remove_provider = $this->as3cf->get_item_handler( Remove_Provider_Handler::get_item_handler_key_name() );
			$remove_provider->handle( $as3cf_item, array( 'verify_exists_on_local' => false ) );
			$as3cf_item->delete();
		}
	}

	/**
	 * Removes avatar and cover from remote bucket when a user is deleted
	 *
	 * @handles deleted_user
	 *
	 * @param int $user_id
	 */
	public function handle_deleted_user( $user_id ) {
		$args = array( 'item_id' => $user_id, 'object' => 'user' );
		$this->delete_existing_avatar( $args );
		$this->delete_existing_cover( $user_id, 'user' );
	}

	/**
	 * Removes avatar and cover when a group is deleted
	 *
	 * @handles groups_delete_group
	 *
	 * @param int $group_id
	 */
	public function groups_delete_group( $group_id ) {
		$args = array( 'item_id' => $group_id, 'object' => 'group' );
		$this->delete_existing_avatar( $args );
		$this->delete_existing_cover( $group_id, 'group' );
	}

	/**
	 * Removes an avatar from the remote bucket
	 *
	 * @handles bp_core_delete_existing_avatar
	 *
	 * @param array $args
	 */
	public function delete_existing_avatar( $args ) {
		if ( ! isset( $args['item_id'] ) || ! is_numeric( $args['item_id'] ) || empty( $args['object'] ) ) {
			return;
		}

		/** @var BBoss_Item $as3cf_item */
		$as3cf_item = BBoss_Item::get_buddy_boss_item( $args['item_id'], $args['object'], 'avatar' );
		if ( ! empty( $as3cf_item ) ) {
			$remove_provider = $this->as3cf->get_item_handler( Remove_Provider_Handler::get_item_handler_key_name() );
			$remove_provider->handle( $as3cf_item, array( 'verify_exists_on_local' => false ) );
			$as3cf_item->delete();
		}
	}

	/**
	 * Identifies URLs to avatars and cover images and rewrites the URL to
	 * the size neutral version.
	 *
	 * @handles as3cf_remove_size_from_filename
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function remove_size_from_filename( $url ) {
		$found_match = false;
		foreach ( $this->source_types as $source_type ) {
			/** @var BBoss_Item $class */
			$class   = $source_type['class'];
			$pattern = sprintf(
				'/\/%s\/[0-9a-f]{9,14}-bp(full|thumb|\-cover\-image)\./',
				str_replace( '%d', '\d+', preg_quote( $class::get_prefix_pattern(), '/' ) )
			);

			$match = preg_match( $pattern, $url );
			if ( ! empty( $match ) ) {
				$found_match = true;
				break;
			}
		}

		if ( ! $found_match ) {
			return $url;
		}

		return BBoss_Item::remove_size_from_filename( $url );
	}

	/**
	 * Get the size from a URL for the Buddy Boss item types
	 *
	 * @handles as3cf_get_size_string_from_url_for_item_source
	 *
	 * @param string $size
	 * @param string $url
	 * @param array  $item_source
	 *
	 * @return string
	 */
	public function get_size_string_from_url_for_item_source( $size, $url, $item_source ) {
		if ( ! in_array( $item_source['source_type'], array_keys( $this->source_types ), true ) ) {
			return $size;
		}

		return static::get_object_key_from_filename( $url );
	}

	/**
	 * Return size name based on the file name
	 *
	 * @param string $filename
	 *
	 * @return string | null
	 */
	public static function get_object_key_from_filename( $filename ) {
		$size     = Item::primary_object_key();
		$filename = preg_replace( '/\?.*/', '', $filename );
		$basename = AS3CF_Utils::encode_filename_in_path( wp_basename( $filename ) );

		if ( false !== strpos( $basename, '-bpthumb' ) ) {
			$size = 'thumb';
		}

		return $size;
	}

	/**
	 * Get the remote URL for a User / Group avatar or cover image
	 *
	 * @handles as3cf_get_provider_url_for_item_source
	 *
	 * @param string $url         Url
	 * @param array  $item_source The item source descriptor array
	 * @param string $size        Name of requested size
	 *
	 * @return string
	 */
	public function filter_get_provider_url_for_item_source( $url, $item_source, $size ) {
		if ( Item::is_empty_item_source( $item_source ) ) {
			return $url;
		}

		if ( ! in_array( $item_source['source_type'], array_keys( $this->source_types ), true ) ) {
			return $url;
		}

		/** @var BBoss_Item $class */
		$class = ! empty( $this->source_types[ $item_source['source_type'] ] ) ? $this->source_types[ $item_source['source_type'] ]['class'] : false;
		if ( false !== $class ) {
			if ( empty( $size ) ) {
				$size = Item::primary_object_key();
			}

			$as3cf_item = $class::get_by_source_id( $item_source['id'] );
			if ( empty( $as3cf_item ) || ! $as3cf_item->served_by_provider() ) {
				return $url;
			}

			$url = $as3cf_item->get_provider_url( $size );
		}

		return $url;
	}

	/**
	 * Get the local URL for a User / Group avatar or cover image
	 *
	 * @handles as3cf_get_local_url_for_item_source
	 *
	 * @param string $url         Url
	 * @param array  $item_source The item source descriptor array
	 * @param string $size        Name of requested size
	 *
	 * @return string
	 */
	public function filter_get_local_url_for_item_source( $url, $item_source, $size ) {
		if ( Item::is_empty_item_source( $item_source ) ) {
			return $url;
		}

		if ( ! in_array( $item_source['source_type'], array_keys( $this->source_types ), true ) ) {
			return $url;
		}

		/** @var BBoss_Item $class */
		$class = ! empty( $this->source_types[ $item_source['source_type'] ] ) ? $this->source_types[ $item_source['source_type'] ]['class'] : false;
		if ( ! empty( $class ) ) {
			if ( empty( $size ) ) {
				$size = Item::primary_object_key();
			}

			$as3cf_item = $class::get_by_source_id( $item_source['id'] );
			if ( empty( $as3cf_item ) ) {
				return $url;
			}

			$url = $as3cf_item->get_local_url( $size );
		}

		return $url;
	}

	/**
	 * Remove fake filename ending from a stripped bucket key
	 *
	 * @handles as3cf_strip_image_edit_suffix_and_extension
	 *
	 * @param string $path
	 * @param string $source_type
	 *
	 * @return string
	 */
	public function filter_strip_image_edit_suffix_and_extension( $path, $source_type ) {
		if ( ! in_array( $source_type, array_keys( $this->source_types ), true ) ) {
			return $path;
		}

		if ( '/bp' === substr( $path, -3 ) ) {
			$path = trailingslashit( dirname( $path ) );
		}

		return $path;
	}

	/**
	 * Handle / override Buddy Boss attempt to delete a local file that we have already removed
	 *
	 * @handles bp_attachments_pre_delete_file
	 *
	 * @param bool  $pre
	 * @param array $args
	 *
	 * @return bool
	 */
	public function bp_attachments_pre_delete_file( $pre, $args ) {
		if ( empty( $args['object_dir'] ) || empty( $args['item_id'] ) ) {
			return $pre;
		}

		$object_type = $this->object_type_from_dir( $args['object_dir'] );
		if ( is_null( $object_type ) ) {
			return $pre;
		}

		/** @var BBoss_Item $class */
		$class      = BBoss_Item::get_item_class( $object_type, 'cover' );
		$as3cf_item = $class::get_by_source_id( (int) $args['item_id'] );

		if ( ! $as3cf_item ) {
			return $pre;
		}

		$source_file = $as3cf_item->full_source_path( Item::primary_object_key() );
		if ( file_exists( $source_file ) ) {
			return $pre;
		}

		return false;
	}

	/**
	 * Return object_type (user or group) based on object_dir passed in from Buddy Boss
	 *
	 * @param string $object_dir
	 *
	 * @return string|null
	 */
	private function object_type_from_dir( $object_dir ) {
		switch ( $object_dir ) {
			case 'members':
				return 'user';
			case 'groups':
				return 'group';
		}

		AS3CF_Error::log( 'Unknown object_dir ' . $object_dir );

		return null;
	}
}
