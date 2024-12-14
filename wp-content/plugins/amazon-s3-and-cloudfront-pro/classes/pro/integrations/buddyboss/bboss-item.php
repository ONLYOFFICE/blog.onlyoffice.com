<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss;

use Amazon_S3_And_CloudFront;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DirectoryIterator;
use WP_Error;

class BBoss_Item extends Item {
	/**
	 * Buddy Boss images have random and unique names, object versioning not needed
	 *
	 * @var bool
	 */
	const CAN_USE_OBJECT_VERSIONING = false;

	/**
	 * Item's summary type name.
	 *
	 * @var string
	 */
	protected static $summary_type_name = 'BuddyBoss';

	/**
	 * Item's summary type.
	 *
	 * @var string
	 */
	protected static $summary_type = 'bboss';

	/**
	 * The sprintf() pattern for creating prefix based on source_id.
	 *
	 * @var string
	 */
	protected static $prefix_pattern = '';

	/**
	 * Buddy Boss images are not managed in yearmonth folders
	 *
	 * @var bool
	 */
	protected static $can_use_yearmonth = false;

	/**
	 * @var string
	 */
	protected static $folder = '';

	/**
	 * @var bool
	 */
	protected static $is_cover = false;

	/**
	 * @var bool
	 */
	protected static $is_group = false;

	/**
	 * @var int
	 */
	private static $chunk_size = 1000;

	/**
	 * Get a Buddy Boss item object from the database
	 *
	 * @param int    $source_id
	 * @param string $object_type
	 * @param string $image_type
	 *
	 * @return BBoss_Item|false
	 */
	public static function get_buddy_boss_item( $source_id, $object_type, $image_type ) {
		/** @var BBoss_Item $class */
		$class = static::get_item_class( $object_type, $image_type );

		if ( ! empty( $class ) ) {
			return $class::get_by_source_id( $source_id );
		}

		return false;
	}

	/**
	 * Get the appropriate Buddy Boss item sub class based on object and image type
	 *
	 * @param string $object_type user or group
	 * @param string $image_type  avatar or cover image
	 *
	 * @return false|string
	 */
	public static function get_item_class( $object_type, $image_type ) {
		$class_map = array(
			'user'  => array(
				'avatar' => 'BBoss_User_Avatar',
				'cover'  => 'BBoss_User_Cover',
			),
			'group' => array(
				'avatar' => 'BBoss_Group_Avatar',
				'cover'  => 'BBoss_Group_Cover',
			),
		);

		if ( isset( $class_map[ $object_type ][ $image_type ] ) ) {
			return __NAMESPACE__ . '\\' . $class_map[ $object_type ][ $image_type ];
		} else {
			return false;
		}
	}

	/**
	 * Create a new Buddy Boss item from the source id.
	 *
	 * @param int   $source_id
	 * @param array $options
	 *
	 * @return BBoss_Item|WP_Error
	 */
	public static function create_from_source_id( $source_id, $options = array() ) {
		$file_paths = static::get_local_files( $source_id );
		if ( empty( $file_paths ) ) {
			return new WP_Error(
				'exception',
				__( 'No local files found in ' . __FUNCTION__, 'amazon-s3-and-cloudfront' )
			);
		}

		$file_path = static::remove_size_from_filename( $file_paths[ Item::primary_object_key() ] );

		$extra_info = array( 'objects' => array() );
		foreach ( $file_paths as $key => $path ) {
			$extra_info['objects'][ $key ] = array(
				'source_file' => wp_basename( $path ),
				'is_private'  => false,
			);
		}

		return new static( null, null, null, null, false, $source_id, $file_path, null, $extra_info, self::CAN_USE_OBJECT_VERSIONING );
	}

	/**
	 * Get item's new public prefix path for current settings.
	 *
	 * @param bool $use_object_versioning
	 *
	 * @return string
	 */
	public function get_new_item_prefix( $use_object_versioning = true ) {
		/** @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		// Base prefix from settings
		$prefix = $as3cf->get_object_prefix();
		$prefix .= AS3CF_Utils::trailingslash_prefix( $as3cf->get_dynamic_prefix( null, static::$can_use_yearmonth ) );

		// Buddy Boss specific prefix
		$buddy_boss_prefix = sprintf( static::$prefix_pattern, $this->source_id() );
		$prefix            .= AS3CF_Utils::trailingslash_prefix( $buddy_boss_prefix );

		return $prefix;
	}

	/**
	 * Return all buddy boss file sizes from the source folder
	 *
	 * @param int $source_id
	 *
	 * @return array
	 */
	public static function get_local_files( $source_id ) {
		$basedir = bp_core_get_upload_dir( 'upload_path' );

		// Get base path and apply filters
		if ( static::$is_cover ) {
			// Call filters indirectly via bp_attachments_cover_image_upload_dir()
			$args       = array(
				'object_id'        => $source_id,
				'object_directory' => str_replace( 'buddypress/', '', static::$folder ),
			);
			$upload_dir = bp_attachments_cover_image_upload_dir( $args );
			$image_path = $upload_dir['path'];
		} else {
			// Call apply_filters directly
			$image_path  = trailingslashit( $basedir ) . trailingslashit( static::$folder ) . $source_id;
			$object_type = static::$is_group ? 'group' : 'user';
			$image_path  = apply_filters( 'bp_core_avatar_folder_dir', $image_path, $source_id, $object_type, static::$folder );
		}

		$result = array();

		if ( ! file_exists( $image_path ) ) {
			return $result;
		}

		$files = new DirectoryIterator( $image_path );

		foreach ( $files as $file ) {
			if ( $file->isDot() ) {
				continue;
			}

			$base_name = $file->getFilename();
			$file_name = substr( $file->getPathname(), strlen( $basedir ) );
			$file_name = AS3CF_Utils::unleadingslashit( $file_name );

			if ( false !== strpos( $base_name, '-bp-cover-image' ) ) {
				$result[ Item::primary_object_key() ] = $file_name;
			}
			if ( false !== strpos( $base_name, '-bpfull' ) ) {
				$result[ Item::primary_object_key() ] = $file_name;
			}
			if ( false !== strpos( $base_name, '-bpthumb' ) ) {
				$result['thumb'] = $file_name;
			}
		}

		return $result;
	}

	/**
	 * Buddy Boss specific size removal from URL and convert it to a neutral
	 * (mock) file name with the correct file extension
	 *
	 * @param string $file_name The file
	 *
	 * @return string
	 */
	public static function remove_size_from_filename( $file_name ) {
		$path_info = pathinfo( $file_name );

		return trailingslashit( $path_info['dirname'] ) . 'bp.' . $path_info['extension'];
	}

	/**
	 * Return size name based on the file name
	 *
	 * @param string $filename
	 *
	 * @return string | null
	 */
	public function get_object_key_from_filename( $filename ) {
		return BuddyBoss::get_object_key_from_filename( $filename );
	}

	/**
	 * Get an array of un-managed source_ids in descending order.
	 *
	 * While source id isn't strictly unique, it is by source type, which is always used in queries based on called class.
	 *
	 * @param int  $upper_bound Returned source_ids should be lower than this, use null/0 for no upper bound.
	 * @param int  $limit       Maximum number of source_ids to return. Required if not counting.
	 * @param bool $count       Just return a count of matching source_ids? Negates $limit, default false.
	 *
	 * @return array|int
	 */
	public static function get_missing_source_ids( $upper_bound, $limit, $count = false ) {
		global $wpdb;

		// Bail out with empty values if we are a group class and the groups component is not active
		if ( static::$is_group ) {
			$active_bp_components = apply_filters( 'bp_active_components', bp_get_option( 'bp-active-components' ) );
			if ( empty( $active_bp_components['groups'] ) ) {
				return $count ? 0 : array();
			}
		}

		$source_table  = $wpdb->prefix . static::$source_table;
		$basedir       = bp_core_get_upload_dir( 'upload_path' );
		$dir           = trailingslashit( $basedir ) . static::$folder . '/';
		$missing_items = array();
		$missing_count = 0;

		// Establish an upper bound if needed
		if ( empty( $upper_bound ) ) {
			$sql         = "SELECT max(id) from $source_table";
			$max_id      = (int) $wpdb->get_var( $sql );
			$upper_bound = $max_id + 1;
		}

		for ( $i = $upper_bound; $i >= 0; $i -= self::$chunk_size ) {
			$args   = array();
			$sql    = "
			SELECT t.id as ID from $source_table as t
              LEFT OUTER JOIN " . static::items_table() . " as i
                ON (i.source_id = t.ID AND i.source_type=%s)";
			$args[] = static::source_type();

			$sql    .= ' WHERE i.ID IS NULL AND t.id < %d';
			$args[] = $upper_bound;
			$sql    .= ' ORDER BY t.ID DESC LIMIT %d, %d';
			$args[] = $upper_bound - $i;
			$args[] = self::$chunk_size;
			$sql    = $wpdb->prepare( $sql, $args );

			$items_without_managed_offload = array_map( 'intval', $wpdb->get_col( $sql ) );

			foreach ( $items_without_managed_offload as $item_without_managed_offload ) {
				$target = $dir . $item_without_managed_offload;
				if ( is_dir( $target ) ) {
					if ( $count ) {
						$missing_count++;
					} else {
						$missing_items[] = $item_without_managed_offload;

						// If we have enough items, bail out
						if ( count( $missing_items ) >= $limit ) {
							break 2;
						}
					}
				}
			}
		}

		// Add custom default if available for offload.
		if ( ( $count || count( $missing_items ) < $limit ) && is_dir( $dir . '0' ) ) {
			if ( ! static::get_by_source_id( 0 ) && ! empty( static::get_local_files( 0 ) ) ) {
				if ( $count ) {
					$missing_count++;
				} else {
					$missing_items[] = 0;
				}
			}
		}

		if ( $count ) {
			return $missing_count;
		} else {
			return $missing_items;
		}
	}

	/**
	 * Setter for item's path & original path values
	 *
	 * @param string $path
	 */
	public function set_path( $path ) {
		$path = static::remove_size_from_filename( $path );
		parent::set_path( $path );
		parent::set_original_path( $path );
	}

	/**
	 * Get absolute source file paths for offloaded files.
	 *
	 * @return array Associative array of object_key => path
	 */
	public function full_source_paths() {
		$basedir     = bp_core_get_upload_dir( 'upload_path' );
		$item_folder = dirname( $this->source_path() );

		$objects = $this->objects();
		$sizes   = array();
		foreach ( $objects as $size => $object ) {
			$sizes[ $size ] = trailingslashit( $basedir ) . trailingslashit( $item_folder ) . $object['source_file'];
		}

		return $sizes;
	}

	/**
	 * Get the local URL for an item
	 *
	 * @param string|null $object_key
	 *
	 * @return string
	 */
	public function get_local_url( $object_key = null ) {
		if ( static::$is_cover ) {
			return $this->get_local_cover_url( $object_key );
		} else {
			return $this->get_local_avatar_url( $object_key );
		}
	}

	/**
	 * Get the local URL for an avatar item
	 *
	 * @param string|null $object_key
	 *
	 * @return string
	 */
	protected function get_local_avatar_url( $object_key = null ) {
		$uploads = wp_upload_dir();
		$url     = trailingslashit( $uploads['baseurl'] );
		$url     .= $this->source_path( $object_key );

		return $url;
	}

	/**
	 * Get the local URL for a cover item
	 *
	 * @param string|null $object_key
	 *
	 * @return string
	 */
	protected function get_local_cover_url( $object_key = null ) {
		$uploads = wp_upload_dir();
		$url     = trailingslashit( $uploads['baseurl'] );
		$url     .= $this->source_path( $object_key );

		return $url;
	}

	/**
	 * Get the prefix pattern
	 *
	 * @return string
	 */
	public static function get_prefix_pattern() {
		return static::$prefix_pattern;
	}

	/**
	 * Count total, offloaded and not offloaded items on current site.
	 *
	 * @return array Keys:
	 *               total: Total media count for site (current blog id)
	 *               offloaded: Count of offloaded media for site (current blog id)
	 *               not_offloaded: Difference between total and offloaded
	 */
	protected static function get_item_counts(): array {
		global $wpdb;

		$sql             = 'SELECT count(id) FROM ' . static::items_table() . ' WHERE source_type = %s';
		$sql             = $wpdb->prepare( $sql, static::$source_type );
		$offloaded_count = (int) $wpdb->get_var( $sql );
		$missing_count   = static::get_missing_source_ids( 0, 0, true );

		return array(
			'total'         => $offloaded_count + $missing_count,
			'offloaded'     => $offloaded_count,
			'not_offloaded' => $missing_count,
		);
	}
}
