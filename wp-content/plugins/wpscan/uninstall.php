<?php

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( is_multisite() ) {
	global $wpdb;

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			foreach ( wp_load_alloptions() as $option => $value ) {
				if ( strpos( $option, 'wpscan_' ) === 0 ) {
					delete_option( $option );
				}
			}
			$all_user_ids = get_users( 'fields=ID' );
			foreach ( $all_user_ids as $user_id ) {
				delete_user_meta( $user_id, 'protect_notice_dismissed' );
			}
		}
		restore_current_blog();
	}
} else {
	foreach ( wp_load_alloptions() as $option => $value ) {
		if ( strpos( $option, 'wpscan_' ) === 0 ) {
			delete_option( $option );
		}
	}
	$all_user_ids = get_users( 'fields=ID' );
	foreach ( $all_user_ids as $user_id ) {
		delete_user_meta( $user_id, 'protect_notice_dismissed' );
	}
}
