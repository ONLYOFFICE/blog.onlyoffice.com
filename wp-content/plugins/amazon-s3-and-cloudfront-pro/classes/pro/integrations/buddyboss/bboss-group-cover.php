<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss;

class BBoss_Group_Cover extends BBoss_Item {
	/**
	 * Source type name
	 *
	 * @var string
	 */
	protected static $source_type_name = 'Buddy Boss Group Cover';

	/**
	 * Internal source type identifier
	 *
	 * @var string
	 */
	protected static $source_type = 'bboss-group-cover';

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
	 * @var bool
	 */
	protected static $is_cover = true;

	/**
	 * @var bool
	 */
	protected static $is_group = true;

	/**
	 * Relative folder where group covers are stored on disk
	 *
	 * @var string
	 */
	protected static $folder = 'buddypress/groups';

	/**
	 * The sprintf() pattern for creating prefix based on source_id.
	 *
	 * @var string
	 */
	protected static $prefix_pattern = 'buddypress/groups/%d/cover-image';

	/**
	 * Returns a link to the items edit page in WordPress
	 *
	 * @param object $error
	 *
	 * @return object|null Object containing url and link text
	 */
	public static function admin_link( $error ) {
		return BBoss_Group_Avatar::admin_link( $error );
	}
}
