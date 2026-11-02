<?php

namespace DemocracyPoll;

use RuntimeException;

/**
 * Represents the current visitor state for a specific poll.
 *
 * @property string $voted_for             Voted answer IDs, separated by commas.
 * @property bool   $has_voted             Has the current user voted?
 * @property bool   $voting_blocked        Is the voting blocked? If true, the user cannot vote.
 * @property bool   $blocked_by_not_logged Is blocked because only logged users can vote.
 */
class Poll_User_State {

	private Poll $poll; /* readonly */
	public Poll_Cookies $poll_cookie; /* readonly */
	public Poll_Logs $poll_logs; /* readonly */

	private ?bool $blocked_by_not_logged = null;
	private ?bool $voting_blocked = null;
	private ?bool $has_voted = null;
	private ?string $voted_for = null;

	public function __construct( Poll $poll ) {
		$this->poll = $poll;
		$this->poll_cookie = new Poll_Cookies( $poll );
		$this->poll_logs = new Poll_Logs( $poll );
	}

	public function __get( $name ) {
		if( 'voted_for' === $name ){ /** @see self::$voted_for - get */
			return $this->voted_for ??= $this->resolve_voted_for();
		}

		if( 'has_voted' === $name ){ /** @see self::$has_voted - get */
			return $this->has_voted ??= (bool) $this->__get( 'voted_for' );
		}

		if( 'voting_blocked' === $name ){ /** @see self::$voting_blocked - get */
			return $this->voting_blocked ??= ! $this->poll->id || ! $this->poll->open
				|| $this->__get( 'blocked_by_not_logged' )
				|| $this->__get( 'has_voted' );
		}

		if( 'blocked_by_not_logged' === $name ){ /** @see self::$blocked_by_not_logged - get */
			return $this->blocked_by_not_logged ??= $this->poll->id
				&& ( container()->get( Options::class )->only_for_users || $this->poll->forusers )
				&& ! is_user_logged_in();
		}

		throw new RuntimeException( __CLASS__ . " class has no `$name` property." );
	}

	public function __set( $name, $value ) {
		if( 'voted_for' === $name ){  /** @see self::$voted_for - set */
			$this->voted_for = (string) $value;
			$this->has_voted = (bool) $this->voted_for;
		}
		elseif( 'has_voted' === $name ){  /** @see self::$has_voted - set */
			$this->has_voted = (bool) $value;
		}
		elseif( 'blocked_by_not_logged' === $name ){  /** @see self::$blocked_by_not_logged - set */
			$this->blocked_by_not_logged = (bool) $value;
			if( $this->blocked_by_not_logged ){
				$this->voting_blocked = true;
			}
		}
		elseif( 'voting_blocked' === $name ){  /** @see self::$voting_blocked - set */
			$this->voting_blocked = (bool) $value;
		}
		else {
			throw new RuntimeException( __CLASS__ . " class has no `$name` property." );
		}
	}

	public function __isset( $name ) {
		if( in_array( $name, [ 'blocked_by_not_logged', 'voting_blocked', 'has_voted', 'voted_for' ], true ) ){
			$this->__get( $name );
			return true;
		}

		return false;
	}

	public function set_vote_cookie(): void {
		$this->poll_cookie->set();
	}

	public function set_not_voted_cookie(): void {
		$this->poll_cookie->set_not_voted();
	}

	private function resolve_voted_for(): string {
		if( ! $this->poll->id ){
			return '';
		}

		// The database takes precedence over cookies, because in one browser you can cancel the vote,
		// but in another browser cookies will still show that you have voted...
		// NOTE: update cookies if they do not match. Because in different browsers they can be different. Does not work,
		// because cookies need to be set before outputting data, and in general, this should not be done, because checking
		// by cookies becomes unnecessary overall...
		if( $logs = $this->poll_logs->get_user_vote_logs() ){
			return (string) reset( $logs )->aids;
		}

		if( $this->poll_logs->is_identity_resolved() ){
			return '';
		}

		if( ! $this->poll_cookie->is_not_voted() ){
			return $this->poll_cookie->get();
		}

		return '';
	}

}
