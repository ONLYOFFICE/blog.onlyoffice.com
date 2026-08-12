<?php

namespace DemocracyPoll\System;

use DemocracyPoll\Options_CSS;
use DemocracyPoll\Options;
use DemocracyPoll\Plugin;

class Upgrader {

	public const VER_OPT_NAME = 'democracy_version';

	private const LOCK_OPT_NAME = 'democracy_upgrade_lock';
	private const LOCK_TIMEOUT = 300;

	private Plugin $plugin;
	private Options_CSS $options_css;
	private $old_ver;

	public function __construct( Plugin $plugin, Options_CSS $options_css ) {
		$this->plugin = $plugin;
		$this->options_css = $options_css;
		$this->old_ver = get_option( self::VER_OPT_NAME );
	}

	public function upgrade_force(): void {
		$this->old_ver = '0.1';
		update_option( self::VER_OPT_NAME, $this->old_ver );
		$this->upgrade();
	}

	public function upgrade(): void {
		if( $this->old_ver === $this->plugin->ver ){
			return;
		}

		if( ! $this->acquire_lock() ){
			return;
		}

		try {
			// Another request may have completed the upgrade before we acquired the lock.
			if( get_option( self::VER_OPT_NAME ) === $this->plugin->ver ){
				return;
			}

			$this->options_css->regenerate_democracy_css( null );
			$this->run_migrations();

			update_option( self::VER_OPT_NAME, $this->plugin->ver );
		}
		finally {
			delete_option( self::LOCK_OPT_NAME );
		}
	}

	/**
	 * Creates a lock for the upgrade.
	 * Returns false if another request already created the lock.
	 */
	private function acquire_lock(): bool {
		if( add_option( self::LOCK_OPT_NAME, time(), '', false ) ){
			return true;
		}

		$lock_time = (int) get_option( self::LOCK_OPT_NAME );
		if( $lock_time + self::LOCK_TIMEOUT >= time() ){
			return false;
		}

		delete_option( self::LOCK_OPT_NAME );

		return (bool) add_option( self::LOCK_OPT_NAME, time(), '', false );
	}

	private function run_migrations(): void {
		global $wpdb;

		$cols_q = $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->democracy_q", OBJECT_K );
		$fields_q = array_keys( $cols_q );

		$cols_a = $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->democracy_a", OBJECT_K );
		$fields_a = array_keys( $cols_a );

		$cols_log = $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->democracy_log", OBJECT_K );
		$fields_log = array_keys( $cols_log );

		// 3.1.3
		if( ! in_array( 'end', $fields_q, true ) ){
			$wpdb->query( "ALTER TABLE $wpdb->democracy_q ADD `end` int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `added`;" );
		}

		if( ! in_array( 'note', $fields_q, true ) ){
			$wpdb->query( "ALTER TABLE $wpdb->democracy_q ADD `note` text NOT NULL;" );
		}

		if( in_array( 'current', $fields_q, true ) ){
			$wpdb->query( "ALTER TABLE $wpdb->democracy_q CHANGE `current` `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0;" );
			$wpdb->query( "ALTER TABLE $wpdb->democracy_q CHANGE `active` `open`    tinyint(1) UNSIGNED NOT NULL DEFAULT 0;" );
		}

		// 4.1
		if( ! in_array( 'aids', $fields_log, true ) ){
			// Add two fields and their indexes when the aids field is missing.
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log ADD `aids`   text NOT NULL;" );
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log ADD `userid` bigint(20) UNSIGNED NOT NULL DEFAULT 0;" );
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log ADD KEY userid (userid)" );
		}

		if( ! in_array( 'fingerprint', $fields_log, true ) ){
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log ADD `fingerprint` char(64) NOT NULL DEFAULT '';" );
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log DROP INDEX qid" );
			$wpdb->query( "ALTER TABLE $wpdb->democracy_log ADD KEY qid_fingerprint (qid,fingerprint)" );
		}

		$options = get_option( Options::OPT_NAME, [] );
		if( isset( $options['keep_logs'] ) ){
			if( ! isset( $options['allow_same_ip_votes'] ) ){
				$options['allow_same_ip_votes'] = (int) empty( $options['keep_logs'] );
			}
			unset( $options['keep_logs'] );
			update_option( Options::OPT_NAME, $options );
		}
	}

}
