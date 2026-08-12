<?php

namespace DemocracyPoll;

use WP_Error;

/**
 * Handles voting use-cases for a poll.
 */
class Poll_Voting {

	private Poll $poll;
	private Options $options;

	public function __construct( Poll $poll, Options $options ) {
		$this->poll = $poll;
		$this->options = $options;
	}

	/**
	 * @param string|array $aids Answer IDs separated by "~". May contain a string,
	 *                           which will be added as a user answer.
	 *
	 * @return WP_Error|string IDs separated by commas.
	 */
	public function vote( $aids, string $fingerprint = '' ) {
		$poll = $this->poll;
		$ustate = $poll->user_state;
		$ustate->poll_logs->set_fingerprint( $fingerprint );
		if( ! $poll->id ){
			return new WP_Error( 'vote_err', 'ERROR: no id' );
		}

		// Set the cookie again, there was a bug...
		if( $ustate->has_voted && $ustate->poll_cookie->is_not_voted() ){
			$ustate->poll_cookie->set();
		}

		// Run after the has_voted check; a voted user always has voting_blocked set.
		if( $ustate->voting_blocked ){
			return new WP_Error( 'vote_err', 'ERROR: voting is blocked...' );
		}

		$aids = $this->parse_request_aids( $aids );

		// Check the quantity
		if( $poll->multiple > 1 && count( $aids ) > $poll->multiple ){
			return new WP_Error( 'vote_err', __( 'ERROR: You selected more answers than allowed.', 'democracy-poll' ) );
		}

		// Add user free-answer
		if( $poll->democratic ){
			$aids = $this->add_democratic_answer_if_present( $aids );
		}

		$voted_for = $this->prepare_voted_for( $aids );
		if( is_wp_error( $voted_for ) ){
			return $voted_for;
		}

		if( ! Poll_Storage::increment_votes( $poll, $voted_for ) ){
			return new WP_Error( 'vote_err', 'Internal ERROR: Vote was not saved. Contact developer.' );
		}

		$poll->users_voted++;

		$ustate->voted_for = $voted_for;
		$ustate->has_voted = true;
		$ustate->voting_blocked = true;

		if( ! $ustate->poll_logs->insert_logs() ){
			Poll_Storage::decrement_votes( $poll, $voted_for );
			$poll->users_voted--;
			$ustate->voted_for = '';
			$ustate->has_voted = false;
			$ustate->voting_blocked = ! $poll->open;

			$poll->set_answers();

			return new WP_Error( 'vote_err', 'Internal ERROR: Vote log was not saved. Contact developer.' );
		}

		$poll->set_answers();

		$ustate->poll_cookie->set();

		/**
		 * Allows to perform actions after the user has voted.
		 *
		 * @param string $voted_for  Comma-separated IDs of the answers the user voted for. Or custom answer as string.
		 * @param Poll   $poll       The current poll object.
		 */
		do_action( 'dem_voted', $ustate->voted_for, $poll );

		return $ustate->voted_for;
	}

	/**
	 * Deletes the user's voting data.
	 */
	public function delete_vote( string $fingerprint = '' ): void {
		$poll = $this->poll;
		$ustate = $poll->user_state;
		$ustate->poll_logs->set_fingerprint( $fingerprint );

		if( ! $poll->id || ! $poll->revote ){
			return;
		}

		$logs = $ustate->poll_logs->get_user_vote_logs();
		if( ! $logs ){
			return;
		}

		// Use only server-side voting data. The public cookie is user-controlled.
		$ustate->voted_for = (string) reset( $logs )->aids;
		$ustate->has_voted = (bool) $ustate->voted_for;

		Poll_Storage::decrement_votes( $poll, $ustate->voted_for );
		$ustate->poll_logs->delete_vote_log( $logs );

		$ustate->poll_cookie->delete();

		$ustate->has_voted = false;
		$ustate->voted_for = '';
		$ustate->voting_blocked = ! $poll->open;

		$poll->set_answers(); // if an added answer was deleted

		/**
		 * Allows to perform actions after the user's vote has been deleted.
		 *
		 * @param Poll $poll  The current poll object.
		 */
		do_action( 'dem_vote_deleted', $poll );
	}

	private function parse_request_aids( $aids ): array {
		if( ! is_array( $aids ) ){
			$aids = trim( $aids );
			$aids = explode( '~', $aids );
		}

		$aids = array_map( 'trim', $aids );

		return array_filter( $aids );
	}

	private function add_democratic_answer_if_present( array $aids ): array {
		$poll = $this->poll;
		$new_free_answer = '';

		foreach( $aids as $k => $id ){
			if( is_numeric( $id ) ){
				continue;
			}

			$new_free_answer = $id;
			unset( $aids[ $k ] ); // remove from the common array, so that there is no this answer

			if( ! $poll->multiple ){
				$aids = [];
			}

			//break; // IMP!!!: NO break here
		}

		if( $new_free_answer && ( $aid = Poll_Storage::insert_democratic_answer( $poll, $new_free_answer ) ) ){
			$aids[] = $aid;
		}

		return $aids;
	}

	/**
	 * @return string|WP_Error
	 */
	private function prepare_voted_for( array $aids ) {
		$poll = $this->poll;

		$aids = array_filter( $aids );
		if( ! $aids ){
			return new WP_Error( 'vote_err', 'ERROR: no answer ids after parse request. Contact developer.' );
		}

		if( count( $aids ) === 1 ){
			$aid = (int) reset( $aids );

			return $aid ? (string) $aid : new WP_Error( 'vote_err', 'ERROR: no answer id after parse request. Contact developer.' );
		}

		if( ! $poll->multiple ){
			return new WP_Error( 'vote_err', 'ERROR: Vote allowed for one answer only.' );
		}

		$aids = array_map( 'intval', $aids );
		if( count( $aids ) > $poll->multiple ){
			$aids = array_slice( $aids, 0, $poll->multiple );
		}

		$voted_for = implode( ',', array_filter( $aids ) );

		return $voted_for ?: new WP_Error( 'vote_err', 'ERROR: no answer ids after parse request. Contact developer.' );
	}

}
