<?php

function wls_logs_table() {
	global $wpdb;
	return $wpdb->base_prefix.'wls_logs';
}


function wls_entries_table() {
	global $wpdb;
	return $wpdb->base_prefix.'wls_entries';
}


function wls_plugin_activation() {
	global $wpdb;

	$logs_structure =
	'CREATE TABLE IF NOT EXISTS '.wls_logs_table().' (
            id INT NOT NULL AUTO_INCREMENT,
            log_name VARCHAR(255),
            description LONGTEXT,
            UNIQUE ( id ),
            PRIMARY KEY ( id )
        )';

	$wpdb->query( $logs_structure );

	$entries_structure =
	'CREATE TABLE IF NOT EXISTS '.wls_entries_table().' (
            id INT NOT NULL AUTO_INCREMENT,
            log_id INT,
            blog_id BIGINT(20),
            date DATETIME,
            user_id BIGINT(20),
            category INT,
            text LONGTEXT,
            seen BOOL DEFAULT FALSE,
            UNIQUE ( id ),
            PRIMARY KEY ( id )
        )';

	$wpdb->query( $entries_structure );

	wls_set_version( WLS_VERSION );
}

function wls_get_log_id( $log_name ) {
	global $wpdb;
	$query = 'SELECT id
		FROM '.wls_logs_table().'
		WHERE log_name LIKE %s
		LIMIT 1';
	$log_id = $wpdb->get_var( $wpdb->prepare( $query, $log_name ) );
	return $log_id;
}

function wls_get_logs( ) {
	global $wpdb;
	$query = 'SELECT *
		FROM '.wls_logs_table().'
		ORDER BY log_name ASC';
	return $wpdb->get_results( $query );
}


function wls_get_log( $log_id ) {
	global $wpdb;
	$query = 'SELECT *
		FROM '.wls_logs_table().'
		WHERE id = %d';
	return $wpdb->get_row( $wpdb->prepare( $query, $log_id ) );
}


/**
 * @param $log_id log id or 'all' for all logs
 * @param $seen if true, will show all entries. if false, only unseen
 */
function wls_get_entries( $log_id = 'all', $from = 0, $to = 100, $seen = true, $order = 'ASC', $min_severity = 0,
		$what = 'entries', $entry_id = 0 ) {

	global $wpdb;

	/* Determine what will be selected. */
	$count_only = false;
	if( $what == 'entries' ) {
		$what = '*';
	} else if( $what == 'count' ) {
		$what = 'COUNT(*)';
		$count_only = true;
	}

	/* WHERE conditions */
	$cond = array();
	if( $log_id != 'all' ) {
		$cond[] = 'log_id = '.$log_id;
	}
	if( !$seen ) {
		$cond[] = 'seen = 0';
	}
	$cond[] = 'category >= '.$min_severity;
	if( $entry_id > 0 ) {
		$cond[] = 'id = '.$entry_id;
	}
	if( count( $cond ) > 0 ) {
		$where = ' WHERE '.implode( ' AND ', $cond );
	} else {
		$where = '';
	}

	/* LIMIT (ignore if we want record count only). */
	if( $count_only ) {
		$limit = "";
	} else {
		$limit = "LIMIT $from, $to";
	}

	/* ORDER BY (ignore if we want record count only or $order is "NONE" */
	if( $count_only || $order == "NONE") {
		$orderby = "";
	} else {
		$orderby = "ORDER BY date $order, id $order";
	}

	/* Build a query string */
	$query = "SELECT $what
	FROM ".wls_entries_table()."
	$where
	$orderby
	$limit";

	if( $count_only ) {
		return $wpdb->get_var( $query );
	} else {
		return $wpdb->get_results( $query );
	}
}


	/*function wls_get_unseen_entries( $from = 0, $to = 100, $order = 'ASC' ) {
	if( get_current_user_id() != 1 ) {
	return array();
	}
	global $wpdb;
	$query = 'SELECT *
	FROM '.wls_entries_table().'
	WHERE seen = 0
	ORDER BY date '.$order.', id '.$order.'
	LIMIT '.$from.', '.$to;
	$entries = $wpdb->get_results( $wpdb->prepare( $query ) );
	return $entries;
	}*/


function wls_get_entry_count( $log_id ) {
	global $wpdb;
	$query = 'SELECT COUNT(*)
		FROM '.wls_entries_table().'
		WHERE log_id = %d';
	return $wpdb->get_var( $wpdb->prepare( $query, $log_id ) );
}


function wls_get_unseen_entry_count( $min_category = 'default' ) {
	$settings = wls_get_settings();
	extract( $settings );

	if( get_current_user_id() != $wls_manager_id ) {
		return 0;
	}

	//echo '<br>'.$min_category.' ';

	if( !strcmp( $min_category, 'default' ) ) {
		//echo ' DEFX ';
		$min_category = $def_severity_filter;
	}


	global $wpdb;
	$query = 'SELECT COUNT(*)
		FROM '.wls_entries_table().'
		WHERE seen = 0 AND category >= %d';

	return $wpdb->get_var( $wpdb->prepare( $query, $min_category ) );
}


function wls_mark_seen( $entry_ids ) {
	global $wpdb;
	if( is_array( $entry_ids ) ) {
		$where = "WHERE id IN (".implode( ", ", $entry_ids ).")";
	} else {
		$where = "WHERE id = $entry_ids";
	}
	$updated = $wpdb->query(
		"UPDATE ".wls_entries_table()." SET seen = 1 $where"
	);
	return $updated;
}


function wls_delete_entries( $entry_ids ) {
	global $wpdb;
	if( is_array( $entry_ids ) ) {
		$where = "WHERE id IN (".implode( ", ", $entry_ids ).")";
	} else {
		$where = "WHERE id = $entry_ids";
	}
	$deleted = $wpdb->query(
		"DELETE FROM ".wls_entries_table()." $where"
	);
	return $deleted;
}

?>