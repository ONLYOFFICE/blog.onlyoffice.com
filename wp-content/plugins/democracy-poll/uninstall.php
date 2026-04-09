<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if( is_multisite() ){
	foreach( get_sites() as $site ){
		switch_to_blog( $site->blog_id );
		democr_delete_plugin();
		restore_current_blog();
	}
}
else{
	democr_delete_plugin();
}


function democr_delete_plugin() {
	global $wpdb;

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}democracy_q" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}democracy_a" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}democracy_log" );

	delete_option( 'widget_democracy' );
	delete_option( 'democracy_options' );
	delete_option( 'democracy_version' );
	delete_option( 'democracy_css' );
	delete_option( 'democracy_l10n' );
	delete_option( 'democracy_migrated' );

	delete_transient( 'democracy_referer' );
}


