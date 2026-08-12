<?php

namespace DemocracyPoll;

/**
 * Stores votes for all polls in one browser cookie.
 * Format: "poll_id:answer_id_answer_id-base36_timestamp|...".
 * Answer ID 0 is the "not voted" marker.
 * Eg: "2:23_43-t44we8|3:45_56-t44whk|5:0-t44wkw".
 */
class Poll_Cookies {

	public const COOKIE_KEY = 'demPoll';

	/**
	 * This is a service marker for "user has not voted", which prevents frequent database checks.
	 * Stored as answer ID 0 and ignored 12 hours after the record timestamp.
	 */
	public const NOT_VOTED = 'notVoted';

	private const NOT_VOTED_TTL = DAY_IN_SECONDS / 2;

	private Poll $poll;

	public static function to_base36( int $number ): string {
		return base_convert( (string) $number, 10, 36 );
	}

	public static function from_base36( string $number ): int {
		return (int) base_convert( $number, 36, 10 );
	}

	public function __construct( Poll $poll ) {
		$this->poll = $poll;
	}

	/**
	 * Gets the cookie value for the current poll.
	 */
	public function get(): string {
		$values = $this->get_values();
		$pid = (string) $this->poll->id;

		if( isset( $values[ $pid ] ) ){
			return $values[ $pid ]['value'];
		}

		return '';
	}

	public function is_not_voted(): bool {
		return $this->get() === self::NOT_VOTED;
	}

	/**
	 * Sets the cookie value for the current poll.
	 */
	public function set(): void {
		$this->set_value( $this->poll->user_state->voted_for );
	}

	public function set_not_voted(): void {
		$this->set_value( self::NOT_VOTED );
	}

	/**
	 * Removes the current poll from the shared cookie.
	 */
	public function delete(): void {
		$values = $this->get_values();
		unset( $values[ (string) $this->poll->id ] );

		if( $values ){
			$this->save_values( $values );
		}
		else{
			$this->send_cookie( self::COOKIE_KEY, '', strtotime( '-1 day' ) );
			unset( $_COOKIE[ self::COOKIE_KEY ] );
		}
	}

	/**
	 * @return array<string, array{value: string, timestamp: int}>
	 */
	private function get_values(): array {
		$raw = (string) ( $_COOKIE[ self::COOKIE_KEY ] ?? '' );
		if( ! $raw ){
			return [];
		}

		$values = [];

		foreach( explode( '|', $raw ) as $record ){
			// 2:23_43-t44we8|3:45_56-t44whk|5:0-t44wkw
			if( ! preg_match( '/^(\d+):(0|[1-9]\d*(?:_[1-9]\d*)*)-([0-9a-z]+)$/', $record, $matches ) ){
				continue;
			}

			$pid = $matches[1];
			$aids = $matches[2];
			$timestamp = self::from_base36( $matches[3] );
			$value = ( '0' === $aids ) ? self::NOT_VOTED : str_replace( '_', ',', $aids );

			if( $timestamp && $this->get_value_expire( $value, $timestamp ) > time() ){
				$values[ $pid ] = [
					'value'     => $value,
					'timestamp' => $timestamp
				];
			}
		}

		return $values;
	}

	/**
	 * Sets the cookie value for the current poll.
	 *
	 * @param string $new_val Answer IDs or the NOT_VOTED marker.
	 */
	private function set_value( string $new_val ): void {
		$values = $this->get_values();
		$pid = (string) $this->poll->id;

		if( self::NOT_VOTED !== $new_val ){
			$new_val = $this->normalize_value( $new_val );
			if( ! $new_val ){
				return;
			}
		}

		$values[ $pid ] = [
			'value'     => $new_val,
			'timestamp' => time(),
		];

		$this->save_values( $values );
	}

	/**
	 * @param array<string, array{value: string, timestamp: int}> $values
	 */
	private function save_values( array $values ): void {
		$records = [];
		$expire = 0;

		foreach( $values as $pid => $data ){
			$aids = ( self::NOT_VOTED === $data['value'] ) ? '0' : str_replace( ',', '_', $data['value'] );
			$records[] = "$pid:$aids-" . self::to_base36( $data['timestamp'] );
			$expire = max( $expire, $this->get_value_expire( $data['value'], $data['timestamp'] ) );
		}

		$value = implode( '|', $records );
		$this->send_cookie( self::COOKIE_KEY, $value, $expire );
		$_COOKIE[ self::COOKIE_KEY ] = $value;
	}

	private function get_value_expire( string $value, int $timestamp ): int {
		return $timestamp + ( self::NOT_VOTED === $value
			? self::NOT_VOTED_TTL
			: (int) ( (float) container()->get( Options::class )->cookie_days * DAY_IN_SECONDS )
		);
	}

	private function normalize_value( $value ): string {
		if( ! $value ){
			return '';
		}

		$answer_ids = array_map( 'intval', preg_split( '/\s*,\s*/', $value ) );
		$answer_ids = array_filter( $answer_ids );

		return implode( ',', $answer_ids );
	}

	protected function send_cookie( string $name, string $value, int $expire ): void {
		setrawcookie( $name, $value, $expire, COOKIEPATH );
	}

}
