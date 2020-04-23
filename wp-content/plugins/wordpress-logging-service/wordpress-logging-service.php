<?php
/*
Plugin Name: Wordpress Logging Service
Version: 1.5.4
Description: Provides a simple API for storing miscellaneous log entries and their overview in admin area.
Author: Zaantar
Author URI: http://zaantar.eu
License: GPL2
Donate link: http://zaantar.eu/financni-prispevek
Plugin URI: http://wordpress.org/extend/plugins/wordpress-logging-service
*/

/*  Copyright 2011-2013 Zaantar (email: zaantar@zaantar.eu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// TODO exporty logů, custom fields(?), automatické promazávání (nastavení # záznamů/čas), pro každý log datum "last reviewed"

define( 'WLS_VERSION', '1.5.4' );

require_once plugin_dir_path( __FILE__ ).'includes/admin-overview.php';
require_once plugin_dir_path( __FILE__ ).'includes/admin-overview-table.php';
require_once plugin_dir_path( __FILE__ ).'includes/database.php';
require_once plugin_dir_path( __FILE__ ).'includes/options.php';


register_activation_hook( __FILE__, 'wls_plugin_activation' );


/*****************************************************************************\
 	ADMIN CSS
\*****************************************************************************/


add_action( "admin_enqueue_scripts", "wls_admin_enqueue_styles" );


function wls_admin_enqueue_styles() {
	$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	if( $page == 'wls-superadmin-overview' ) {
		wp_enqueue_style( "wls-admin-style", plugins_url( "includes/style.css", __FILE__ ) );
	}
}


/*****************************************************************************\
		I18N
\*****************************************************************************/


define( 'WLS_TEXTDOMAIN', 'wordpress-logging-service' );
define( "WLS_TXD", WLS_TEXTDOMAIN );

add_action( 'init', 'wls_load_textdomain' );

function wls_load_textdomain() {
	$plugin_dir = basename( dirname(__FILE__) );
	load_plugin_textdomain( WLS_TEXTDOMAIN, false, $plugin_dir.'/languages' );
}


/*****************************************************************************\
		SELF_LOG
\*****************************************************************************/


function wls_selflog( $message, $category = 1 ) {
	if( !wls_is_registered( 'wordpress-logging-service' ) ) {
		$filename = dirname(__FILE__).'/log.txt';
		$file = fopen( $filename, 'ä́' );
		fwrite( $file, $message );
		fclose( $file );
	} else {
		wls_simple_log( 'wordpress-logging-service', $message, $category );
	}
}


/*****************************************************************************\
		UPGRADING
\*****************************************************************************/


add_action( 'plugins_loaded', 'wls_upgrade_db_check' );

function wls_upgrade_db_check() {
	$version = wls_get_version();
	$vp = wls_get_version_parts( $version );
	$cvp = wls_get_version_parts( WLS_VERSION );
	if( ( $vp[0] >= 1 ) && ( $vp[1] >= 4 ) ) {
		return;
	} else {
		wls_selflog( 'Version has changed (from '.$version.' to '.WLS_VERSION.'), upgrade is neccessary.', 3 );

		wls_upgrade_from_first_to_1_4_0();

		$new_version = wls_get_version();
		wls_selflog( 'Current version is now '.$new_version.'.' );

		wls_upgrade_db_check();
	}
}

		
function wls_get_version_parts( $version ) {
	$parts = explode( '.', $version );
	if( count( $parts ) < 2 ) {
		$parts[1] = 0;
	}
	if( count( $parts ) < 3 ) {
		$parts[2] = 0;
	}
	if( !is_int( $parts[2] ) ) {
		$parts[2] = preg_replace ('/[^\d\s]/', '', $parts[2] );
	}
	return $parts;
}


function wls_set_version( $version ) {
	update_site_option( 'wls_version', $version );
}

function wls_get_version() {
	return get_site_option( 'wls_version', '1.3.0' /* first version WLS takes into consideration */ );
}


function wls_upgrade_from_first_to_1_4_0() {
	wls_selflog( 'Performing database upgrade to version 1.4.0.' );
	
	// zaregistrujeme log sebe sama
	wls_register( 'wordpress-logging-service', _e( 'Log entries of Wordpress Logging Service', WLS_TEXTDOMAIN ) ); //'Systémové záznamy pluginu Wordpress Logging Service'
	
	// pridame sloupec 'seen'
	if ( !function_exists( 'maybe_add_column' ) ) {
		require_once(ABSPATH . 'wp-admin/install-helper.php');
	}
	maybe_add_column( wls_entries_table(), 'seen', 'ALTER TABLE '.wls_entries_table().' ADD COLUMN seen BOOL DEFAULT FALSE' );
	
	wls_set_version( '1.4.0' );
}

/*****************************************************************************\
		PUBLIC API
\*****************************************************************************/

define( 'WLS', true );

define( 'WLS_NOCATEGORY', 0 );
define( 'WLS_INFO', 1 );
define( 'WLS_NOTICE', 2 );
define( 'WLS_WARNING', 3 );
define( 'WLS_ERROR', 4 );
define( 'WLS_FATALERROR', 5 );

function wls_is_registered( $log_name ) {
	global $wpdb;
	$query = '
		SELECT COUNT(*)
		FROM '.wls_logs_table().'
		WHERE log_name LIKE %s';
	return ( $wpdb->get_var( $wpdb->prepare( $query, $log_name ) ) > 0 );
}

function wls_register( $log_name, $description ) {
	global $wpdb;
	if( wls_is_registered( $log_name ) ) {
		return false;
	}
	$ok = $wpdb->insert( wls_logs_table(),
		array( 'log_name' => $log_name, 'description' => $description ) );
	return ( $ok != false );
}

function wls_unregister( $log_name ) {
	global $wpdb;
	if( !wls_is_registered( $log_name ) ) {
		return false;
	}
	wls_clear( $log_name );
	$query = 'DELETE FROM '.wls_logs_table().'
		WHERE log_name LIKE %s';
	return ( $wpdb->query( $wpdb->prepare( $query, $log_name ) ) != FALSE );
}

function wls_clear( $log_name ) {
	global $wpdb;
	if( !wls_is_registered( $log_name ) ) {
		return false;
	}
	$query = 'DELETE FROM '.wls_entries_table().'
		WHERE log_id = %d';
	return ( $wpdb->query( $wpdb->prepare( $query, wls_get_log_id( $log_name ) ) ) != FALSE );
}

function wls_log( $log_name, $text, $user_id, $date, $blog_id, $category = WLS_NOCATEGORY ) {
	if( !wls_is_registered( $log_name ) ) {
		return false;
	}
	$data = array(
		'log_id' => wls_get_log_id( $log_name ),
		'blog_id' => $blog_id,
		'date' => $date,
		'user_id' => $user_id,
		'text' => stripslashes( $text ),
		'category' => $category
	);
	
	$lastlog = wls_get_last_log();
	if( $lastlog == NULL ) {
		$ok = true;
		wls_update_last_log( $data );
	} else if( ( $lastlog['log_data']['log_id'] != $data['log_id'] )
		or ( $lastlog['log_data']['blog_id'] != $data['blog_id'] )
		or ( $lastlog['log_data']['user_id'] != $data['user_id'] )
		or ( $lastlog['log_data']['category'] != $data['category'] )
		or ( $lastlog['log_data']['text'] != $data['text'] ) ) {
		$ok = wls_flush_last_log();
		wls_update_last_log( $data );
	} else {
		$lastlog['last'] = $data['date'];
		$lastlog['count'] = $lastlog['count']+1;
		update_site_option( 'WLS_LAST_LOG', $lastlog );
		$ok = true;
	}
	return $ok;
}

function wls_simple_log( $log_name, $text, $category = WLS_NOCATEGORY ) {
	return wls_log( $log_name, $text, get_current_user_id(), current_time( "mysql" ), get_current_blog_id(), $category );
}

/*****************************************************************************\
		REPEATING LOG TRIMMING
\*****************************************************************************/

define( 'WLS_LAST_LOG', 'wls_last_log' );


function wls_get_last_log() {
	$log = get_site_option( WLS_LAST_LOG, NULL );
	return $log;
}


function wls_flush_last_log() {
	$log = wls_get_last_log();
	if( $log == NULL ) {
		return true;
	}
	$data = $log['log_data'];
	if( $log['count'] > 1 ) {
		$data['text'] = '('.$log['count'].'x, last occurence @ '.$log['last'].'): '."\n".$data['text'];
	}
	//echo ' FLUSH ';
	$ok = wls_insert_log( $data );
	wls_clear_last_log();
	return $ok;
}


function wls_clear_last_log() {
	update_site_option( WLS_LAST_LOG, NULL );
}

function wls_update_last_log( $data ) {
	$log = array(
		'log_data' => $data,
		'last' => $data['date'],
		'count' => 1
	);
	update_site_option( WLS_LAST_LOG, $log );
}

function wls_overwrite_last_log( $data ) {
	wls_flush_last_log();
	wls_update_last_log( $data );
}

function wls_insert_log( $data ) {
	global $wpdb;
	$ok = $wpdb->insert( wls_entries_table(), $data, array( '%d', '%d', '%s', '%d', '%s', '%d' ) );
	return ( $ok != false );
}




function wls_is_network_mode() {
	return ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) );
}


?>
