<?php

namespace DemocracyPoll\Utils;

use function DemocracyPoll\plugin;

class Activator {

	public static function set_db_tables(): void {
		global $wpdb;
		$wpdb->democracy_q   = $wpdb->prefix . 'democracy_q';
		$wpdb->democracy_a   = $wpdb->prefix . 'democracy_a';
		$wpdb->democracy_log = $wpdb->prefix . 'democracy_log';
	}

	public static function activate(): void {
		plugin()->initor->basic_init();

		if( is_multisite() ){
			$sites = get_sites();
			foreach( $sites as $site ){
				switch_to_blog( $site->blog_id );
				self::_activate();
				restore_current_blog();
			}
		}
		else{
			self::_activate();
		}
	}

	private static function _activate(): void {
		// create tables
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::db_schema() );

		self::add_sample_poll();

		( new Upgrader() )->upgrade();
	}

	private static function add_sample_poll(): void {
		global $wpdb;

		$is_any_poll = $wpdb->get_row( "SELECT * FROM $wpdb->democracy_q LIMIT 1" );
		if( $is_any_poll ){
			return;
		}

		$wpdb->insert( $wpdb->democracy_q, [
			'question'   => __( 'What is the capital city of France?', 'democracy-poll' ),
			'added'      => current_time( 'timestamp' ),
			'added_user' => get_current_user_id(),
			'democratic' => 1,
			'active'     => 1,
			'open'       => 1,
			'revote'     => 1,
		] );

		$qid = $wpdb->insert_id;

		$answers = [
			__( 'Paris', 'democracy-poll' ),
			__( 'Rome', 'democracy-poll' ),
			__( 'Madrid', 'democracy-poll' ),
			__( 'Berlin', 'democracy-poll' ),
			__( 'London', 'democracy-poll' ),
		];

		// create votes
		$allvotes = 0;
		foreach( $answers as $answr ){
			$allvotes += $votes = rand( 0, 100 );
			$wpdb->insert( $wpdb->democracy_a, [ 'votes' => $votes, 'qid' => $qid, 'answer' => $answr ] );
		}

		// 'users_voted' update
		$wpdb->update( $wpdb->democracy_q, [ 'users_voted' => $allvotes ], [ 'id' => $qid ] );
	}

	public static function db_schema(): string {
		global $wpdb;

		$charset_collate = '';

		if( ! empty( $wpdb->charset ) ){
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if( ! empty( $wpdb->collate ) ){
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		return "
		CREATE TABLE $wpdb->democracy_q (
			id            bigint(20) unsigned NOT NULL auto_increment,
			question      text                NOT NULL default '',
			added         int(10)    unsigned NOT NULL default 0,
			added_user    bigint(20) unsigned NOT NULL default 0,
			end           int(10)    unsigned NOT NULL default 0,
			users_voted   bigint(20) unsigned NOT NULL default 0,
			democratic    tinyint(1) unsigned NOT NULL default 0,
			active        tinyint(1) unsigned NOT NULL default 0,
			open          tinyint(1) unsigned NOT NULL default 0,
			multiple      tinyint(5) unsigned NOT NULL default 0,
			forusers      tinyint(1) unsigned NOT NULL default 0,
			revote        tinyint(1) unsigned NOT NULL default 0,
			show_results  tinyint(1) unsigned NOT NULL default 0,
			answers_order varchar(50)         NOT NULL default '',
			in_posts      text                NOT NULL default '',
			note          text                NOT NULL default '',
			PRIMARY KEY  (id),
			KEY active (active)
		) $charset_collate;

		CREATE TABLE $wpdb->democracy_a (
			aid      bigint(20) unsigned NOT NULL auto_increment,
			qid      bigint(20) unsigned NOT NULL default 0,
			answer   text                NOT NULL default '',
			votes    int(10)    unsigned NOT NULL default 0,
			aorder   int(5)     unsigned NOT NULL default 0,
			added_by varchar(100)        NOT NULL default '',
			PRIMARY KEY  (aid),
			KEY qid (qid)
		) $charset_collate;

		CREATE TABLE $wpdb->democracy_log (
			logid    bigint(20)   unsigned NOT NULL auto_increment,
			ip       varchar(100)          NOT NULL default '',
			qid      bigint(20)   unsigned NOT NULL default 0,
			aids     text                  NOT NULL default '',
			userid   bigint(20)   unsigned NOT NULL default 0,
			date     DATETIME              NOT NULL default '0000-00-00 00:00:00',
			expire   bigint(20)   unsigned NOT NULL default 0,
			ip_info  text                  NOT NULL default '',
			PRIMARY KEY  (logid),
			KEY ip (ip,qid),
			KEY qid (qid),
			KEY userid (userid)
		) $charset_collate;
		";
	}

}





