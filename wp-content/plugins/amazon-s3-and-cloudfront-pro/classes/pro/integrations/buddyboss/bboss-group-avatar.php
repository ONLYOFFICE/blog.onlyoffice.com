<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss;

class BBoss_Group_Avatar extends BBoss_Item {
	/**
	 * Source type name
	 *
	 * @var string
	 */
	protected static $source_type_name = 'Buddy Boss Group Avatar';

	/**
	 * Internal source type identifier
	 *
	 * @var string
	 */
	protected static $source_type = 'bboss-group-avatar';

	/**
	 * Table (if any) that corresponds to this source type
	 *
	 * @var string
	 */
	protected static $source_table = 'bp_groups';

	/**
	 * Foreign key (if any) in the $source_table
	 *
	 * @var string
	 */
	protected static $source_fk = 'id';

	/**
	 * Relative folder where group avatars are stored on disk
	 *
	 * @var string
	 */
	protected static $folder = 'group-avatars';

	/**
	 * The sprintf() pattern for creating prefix based on source_id.
	 *
	 * @var string
	 */
	protected static $prefix_pattern = 'group-avatars/%d';

	/**
	 * @var bool
	 */
	protected static $is_group = true;

	/**
	 * Returns a link to the items edit page in WordPress
	 *
	 * @param object $error
	 *
	 * @return object|null Object containing url and link text
	 */
	public static function admin_link( $error ) {
		$url = self_admin_url( 'admin.php?page=bp-groups&action=edit&gid=' . $error->source_id );

		if ( empty( $url ) ) {
			return null;
		}

		return (object) array(
			'url'  => $url,
			'text' => __( 'Edit', 'amazon-s3-and-cloudfront' ),
		);
	}
}
