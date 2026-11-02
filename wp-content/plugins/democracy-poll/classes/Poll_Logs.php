<?php

namespace DemocracyPoll;

use DemocracyPoll\Support\IP;

class Poll_Logs {

	private Poll $poll;
	private string $fingerprint = '';
	private bool $fingerprint_resolved = false;
	private bool $identity_resolved = false;

	public function __construct( Poll $poll ) {
		$this->poll = $poll;
	}

	public function set_fingerprint( string $fingerprint ): void {
		$this->fingerprint = $fingerprint ? hash( 'sha256', $fingerprint ) : '';
		$this->fingerprint_resolved = true;
	}

	public function is_identity_resolved(): bool {
		return $this->identity_resolved;
	}

	/**
	 * Gets the log rows by user ID or IP address.
	 *
	 * @return object[] democracy_log table rows.
	 */
	public function get_user_vote_logs(): array {
		global $wpdb;

		$WHERE = [
			$wpdb->prepare( 'qid = %d', $this->poll->id ),
			$wpdb->prepare( 'expire > %d', time() )
		];

		$user_id = get_current_user_id();
		// Check the user and IP address separately.
		// Otherwise, anonymous users all have ID 0 and would match one another.
		if( $user_id ){
			$this->identity_resolved = true;
			// For registered users only; the IP address is ignored.
			// A user who voted anonymously can vote again after logging in.
			$WHERE[] = $wpdb->prepare( 'userid = %d', $user_id );
		}
		elseif( container()->get( Options::class )->allow_same_ip_votes ){
			if( $this->fingerprint ){
				$this->identity_resolved = true;
				$WHERE[] = $wpdb->prepare( 'userid = 0 AND fingerprint = %s', $this->fingerprint );
			}
			elseif( ! $this->fingerprint_resolved ){
				return [];
			}
			else {
				$this->identity_resolved = true;
				$WHERE[] = $wpdb->prepare( 'userid = 0 AND ip = %s', IP::get_user_ip() );
			}
		}
		else {
			$this->identity_resolved = true;
			$WHERE[] = $wpdb->prepare( 'userid = 0 AND ip = %s', IP::get_user_ip() );
		}

		$WHERE = implode( ' AND ', $WHERE );

		$sql = "SELECT * FROM $wpdb->democracy_log WHERE $WHERE ORDER BY logid DESC";

		return $wpdb->get_results( $sql );
	}

	public function insert_logs(): bool {
		global $wpdb;

		$poll = $this->poll;
		if( ! $poll->id ){
			return false;
		}

		$options = container()->get( Options::class );
		$user_id = (int) get_current_user_id();

		return (bool) $wpdb->insert( $wpdb->democracy_log, [
			'qid'         => $poll->id,
			'aids'        => $poll->user_state->voted_for,
			'userid'      => $user_id,
			'date'        => current_time( 'mysql' ),
			'expire'      => current_time( 'timestamp', $utc = true ) + (int) ( (float) $options->cookie_days * DAY_IN_SECONDS ),
			'ip'          => IP::get_user_ip(),
			'ip_info'     => '',
			'fingerprint' => ( ! $user_id && $options->allow_same_ip_votes ) ? $this->fingerprint : '',
		] );
	}

	/**
	 * Deletes voting records from the logs.
	 *
	 * @param object[] $logs `democracy_log` table rows.
	 */
	public function delete_vote_log( array $logs = [] ): bool {
		global $wpdb;

		$logs = $logs ?: $this->get_user_vote_logs();
		if( ! $logs ){
			return true;
		}

		$delete_log_ids = wp_list_pluck( $logs, 'logid' );
		$logid_IN = implode( ',', array_map( 'intval', $delete_log_ids ) );

		$sql = "DELETE FROM $wpdb->democracy_log WHERE logid IN ( $logid_IN )";

		return (bool) $wpdb->query( $sql );
	}

}
