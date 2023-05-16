<?php

namespace DeliciousBrains\WP_Offload_Media\Pro\Integrations\BuddyBoss;

class BBoss_User_Avatar extends BBoss_Item {
	/**
	 * Source type name
	 *
	 * @var string
	 */
	protected static $source_type_name = 'Buddy Boss User Avatar';

	/**
	 * Internal source type identifier
	 *
	 * @var string
	 */
	protected static $source_type = 'bboss-user-avatar';

	/**
	 * Table (if any) that corresponds to this source type
	 *
	 * @var string
	 */
	protected static $source_table = 'users';

	/**
	 * Foreign key (if any) in the $source_table
	 *
	 * @var string
	 */
	protected static $source_fk = 'id';

	/**
	 * Relative folder where user avatars are stored on disk
	 *
	 * @var string
	 */
	protected static $folder = 'avatars';

	/**
	 * The sprintf() pattern for creating prefix based on source_id.
	 *
	 * @var string
	 */
	protected static $prefix_pattern = 'avatars/%d';

	/**
	 * Returns a link to the items edit page in WordPress
	 *
	 * @param object $error
	 *
	 * @return object|null Object containing url and link text
	 */
	public static function admin_link( $error ) {
		$url = self_admin_url( 'users.php?page=bp-profile-edit&user_id=' . $error->source_id );

		if ( empty( $url ) ) {
			return null;
		}

		return (object) array(
			'url'  => $url,
			'text' => __( 'Edit', 'amazon-s3-and-cloudfront' ),
		);
	}
}
