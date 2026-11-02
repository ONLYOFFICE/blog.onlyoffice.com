<?php
/**
 * This is a legacy part of old "DemPoll" class.
 * It repeats old behavior for back-compatability.
 * Typehint is realized via class_alias() in autoload.php.
 */

namespace DemocracyPoll;

use RuntimeException;

/**
 * Display and vote a separate poll.
 *
 * @property string  $voted_for
 * @property bool    $has_voted
 * @property bool    $voting_blocked
 * @property bool    $blocked_by_not_logged
 *
 * @property string  $votedFor
 * @property bool    $blockVoting
 * @property bool    $blockForVisitor
 */
abstract class DemPoll_Legacy {

	private ?Poll_Renderer $legacy_renderer = null;

	public function __isset( $name ) {
		if( 'renderer' === $name ){
			return (bool) $this->id;
		}

		$map = [
			'votedFor'        => 'voted_for',
			'blockVoting'     => 'voting_blocked',
			'blockForVisitor' => 'blocked_by_not_logged',
		];

		if( isset( $map[ $name ] ) ){
			$name = $map[ $name ];
		}

		if( in_array( $name, [
			'voted_for',
			'has_voted',
			'voting_blocked',
			'blocked_by_not_logged',
		], true ) ){
			$this->user_state->__get( $map[ $name ] ?? $name );
			return true;
		}

		return false;
	}

	public function __get( $name ) {
		if( 'renderer' === $name ){
			return $this->legacy_renderer ??= container()->make( Poll_Renderer::class, [ 'poll' => $this ] );
		}

		if( 'voted_for' === $name || 'votedFor' === $name ){
			return $this->user_state->voted_for;
		}

		if( 'has_voted' === $name ){
			return $this->user_state->has_voted;
		}

		if( 'voting_blocked' === $name || 'blockVoting' === $name ){
			return $this->user_state->voting_blocked;
		}

		if( 'blocked_by_not_logged' === $name || 'blockForVisitor' === $name ){
			return $this->user_state->blocked_by_not_logged;
		}

		return null;
	}

	public function __set( $name, $value ) {
		if( 'voted_for' === $name || 'votedFor' === $name ){
			$this->user_state->voted_for = (string) $value;
		}
		elseif( 'has_voted' === $name ){
			$this->user_state->has_voted = (bool) $value;
		}
		elseif( 'voting_blocked' === $name || 'blockVoting' === $name ){
			$this->user_state->voting_blocked = (bool) $value;
		}
		elseif( 'blocked_by_not_logged' === $name|| 'blockForVisitor' === $name ){
			$this->user_state->blocked_by_not_logged = (bool) $value;
		}
		else {
			throw new RuntimeException( __CLASS__ . " class prohibits setting dynamic properties. You are trying to set `$name`." );
		}
	}

	public static function get_db_data( $poll_id ): ?object {
		return Poll_Storage::get_db_data( $poll_id );
	}

	public function re_set_answers(): void {
		$this->set_answers();
	}

}
