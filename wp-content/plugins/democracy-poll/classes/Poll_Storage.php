<?php

namespace DemocracyPoll;

use DemocracyPoll\Support\IP;
use DemocracyPoll\Support\Kses;

/**
 * Stores and retrieves poll data.
 */
class Poll_Storage {

	/**
	 * @param int|string $poll_id Poll id to get. Specify 'rand' or 'last' for a random or last poll.
	 */
	public static function get_db_data( $poll_id ): ?object {
		global $wpdb;

		if( 'rand' === $poll_id ){
			$poll_data = $wpdb->get_row( "SELECT * FROM $wpdb->democracy_q WHERE active = 1 ORDER BY RAND() LIMIT 1" );
		}
		elseif( 'last' === $poll_id ){
			$poll_data = $wpdb->get_row( "SELECT * FROM $wpdb->democracy_q WHERE open = 1 ORDER BY id DESC LIMIT 1" );
		}
		else {
			$poll_data = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $wpdb->democracy_q WHERE id = %d LIMIT 1", $poll_id
			) );
		}

		/**
		 * Allows to modify the poll object before it is returned.
		 *
		 * @param object|null $poll_data Raw poll data from DB.
		 */
		return apply_filters( 'dem_get_poll', $poll_data );
	}

	public static function close_if_expired( Poll $poll ): void {
		global $wpdb;

		if( $poll->open && $poll->end && ( current_time( 'timestamp' ) > $poll->end ) ){
			$wpdb->update( $wpdb->democracy_q, [ 'open' => 0 ], [ 'id' => $poll->id ] );
			$poll->open = false;
		}
	}

	/**
	 * Gets answers from DB, sorts them in the required order, and maps them to answer objects.
	 *
	 * @return Poll_Answer[]
	 */
	public static function get_answers( Poll $poll ): array {
		global $wpdb;

		$answers = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->democracy_a WHERE qid = %d", $poll->id
		) );

		$is_custom_order = (bool) ( reset( $answers )->aorder ?? 0 );
		if( $is_custom_order ){
			$answers = wp_list_sort( $answers, [ 'aorder' => 'asc' ] );
		}
		else{
			$order = $poll->answers_order ?: container()->get( Options::class )->order_answers;

			if( $order === 'by_winner' || $order == 1 ){
				$answers = wp_list_sort( $answers, [ 'votes' => 'desc' ] );
			}
			elseif( $order === 'alphabet' ){
				$answers = wp_list_sort( $answers, [ 'answer' => 'asc' ] );
			}
			elseif( $order === 'mix' ){
				shuffle( $answers );
			}
			elseif( $order === 'by_id' ){}
		}

		$answers = array_map( static fn( $data ) => new Poll_Answer( $data ), $answers );

		/**
		 * Allows modifying the answers before they are set in the poll object.
		 *
		 * @param Poll_Answer[] $answers  The answers to be set for the poll.
		 * @param Poll          $poll     The poll object itself.
		 */
		return apply_filters( 'dem_set_answers', $answers, $poll );
	}

	public static function increment_votes( Poll $poll, string $voted_for ): bool {
		global $wpdb;

		$aids = Poll_Utils::parse_voted_str( $voted_for );
		if( ! $aids ){
			return false;
		}

		if( count( $aids ) === 1 ){
			$AND_clause = $wpdb->prepare( ' AND aid = %d LIMIT 1', reset( $aids ) );
		}
		elseif( $poll->multiple ){
			$AND_clause = ' AND aid IN (' . implode( ',', array_map( 'intval', $aids ) ) . ')';
		}
		else{
			return false;
		}

		$answers_updated = $wpdb->query( $wpdb->prepare(
			"UPDATE $wpdb->democracy_a SET votes = (votes+1) WHERE qid = %d $AND_clause", $poll->id
		) );
		$poll_updated = $wpdb->query( $wpdb->prepare(
			"UPDATE $wpdb->democracy_q SET users_voted = (users_voted+1) WHERE id = %d", $poll->id
		) );

		return false !== $answers_updated && false !== $poll_updated;
	}

	/**
	 * Removes votes from the database and deletes the answer if it has 0 or 1 votes.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function decrement_votes( Poll $poll, string $voted_for ): bool {
		global $wpdb;

		$aids = Poll_Utils::parse_voted_str( $voted_for );
		if( ! $aids ){
			return false;
		}

		$aids_IN = implode( ',', array_map( 'intval', $aids ) );

		// first, delete user-added answers if exist and have 0 or 1 votes.
		$r1 = $wpdb->query(
			"DELETE FROM $wpdb->democracy_a WHERE qid = $poll->id AND added_by != '' AND votes IN (0,1) AND aid IN ($aids_IN) ORDER BY aid DESC LIMIT 1"
		);

		// subtract votes
		$r2 = $wpdb->query(
			"UPDATE $wpdb->democracy_a SET votes = IF( votes>0, votes-1, 0 ) WHERE qid = $poll->id AND aid IN ($aids_IN)"
		);

		// subtract number of voted users
		$r3 = $wpdb->query(
			"UPDATE $wpdb->democracy_q SET users_voted = IF( users_voted>0, users_voted-1, 0 ) WHERE id = $poll->id"
		);

		return (bool) ( $r1 || $r2 );
	}

	public static function insert_democratic_answer( Poll $poll, string $answer ): int {
		global $wpdb;

		$new_answer = Kses::sanitize_answer_data( $answer, 'democratic_answer' );
		$new_answer = wp_unslash( $new_answer );

		// check if the answer already exists
		$aids = $wpdb->query( $wpdb->prepare(
			"SELECT aid FROM $wpdb->democracy_a WHERE answer = %s AND qid = %d",
			$new_answer, $poll->id
		) );

		if( $aids ){
			return 0;
		}

		$cuser_id = get_current_user_id();

		// Added from the front end as a democratic answer, regardless of the user.
		$added_by = $cuser_id ?: IP::get_user_ip();
		$added_by .= ( ! $cuser_id || (int) $poll->added_user !== (int) $cuser_id ) ? '-new' : '';

		// if order specified, set 'max+1'
		$answers = $poll->answers;
		$aorder = ( $answers && $answers[0]->aorder > 0 )
			? max( wp_list_pluck( $answers, 'aorder' ) ) + 1
			: 0;

		$inserted = $wpdb->insert( $wpdb->democracy_a, [
			'qid'      => $poll->id,
			'answer'   => $new_answer,
			'votes'    => 0,
			'added_by' => $added_by,
			'aorder'   => $aorder,
		] );

		return $inserted ? $wpdb->insert_id : 0;
	}

}
