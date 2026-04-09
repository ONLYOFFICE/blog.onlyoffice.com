<?php

use DemocracyPoll\Helpers\Helpers;
use DemocracyPoll\Poll_Answer;
use DemocracyPoll\Poll_Renderer;
use DemocracyPoll\Poll_Service;
use function DemocracyPoll\options;

/**
 * Display and vote a separate poll.
 *
 * @property string $voted_for      Voted for answers IDs, separated by commas.
 * @property string $votedFor       Alias of $voted_for. Legacy.
 * @property bool   $has_voted      Is the user has voted?
 * @property bool   $voting_blocked Is the voting blocked? If true, the user cannot vote.
 * @property bool   $blockVoting    Alias of $voting_blocked. Legacy.
 *
 * @property-read array $answers  Answers to the poll, sorted by order.
 *
 * @property-read bool $blockForVisitor  Legacy.
 */
class DemPoll {

	public Poll_Renderer $renderer;
	public Poll_Service $service;

	/**
	 * Flag that means the poll is closed because the user
	 * is not logged and the voting is allowed only for logged users.
	 *
	 * We need this separate property to display a note.
	 */
	public bool $blocked_by_not_logged = false;

	/** Poll data from DB */
	public ?object $dbdata = null;

	/**
	 * Lazy loaded property.
	 * @see self::set_voted_data()
	 */
	private bool $voting_blocked;

	/**
	 * Lazy loaded property.
	 * @see self::set_voted_data()
	 */
	private bool $has_voted;

	/**
	 * Lazy loaded property.
	 * @see self::set_voted_data()
	 */
	private string $voted_for;

	/**
	 * Lazy loaded property.
	 * @see self::set_answers()
	 * @var Poll_Answer[]
	 */
	private array $answers;

	/// DB Fields

	/** Poll ID */
	public int $id = 0;

	/** Poll title */
	public string $question = '';

	/** Added UNIX timestamp */
	public int $added = 0;

	/** End UNIX timestamp */
	public int $end = 0;

	/** User ID */
	public int $added_user = 0;

	/** How many users voted for this poll */
	public int $users_voted = 0;

	/** Is this poll democratic? */
	public bool $democratic = false;

	/** Is this poll active? */
	public bool $active = false;

	/** Is this poll open for voting? */
	public bool $open = false;

	/** How many answers may be selected. */
	public int $multiple = 0;

	/** For logged users only */
	public bool $forusers = false;

	/** Allow to revote */
	public bool $revote = false;

	/** Show results after voting */
	public bool $show_results = false;

	/** Answers order: 'by_winner', 'by_id', 'alphabet', 'mix'. {@see Helpers::allowed_answers_orders()} */
	public string $answers_order = '';

	/** Comma separated posts_ids. Eg: '16865,16892' */
	public string $in_posts = '';

	/** Additional poll notes */
	public string $note = '';

	public function __isset( $name ) {
		// this props canNOT be not set
		$lazy_props = [
			'answers',
			'voting_blocked',
			'blockVoting',
			'voted_for',
			'votedFor',
			'has_voted'
		];
		if( in_array( $name, $lazy_props, true ) ){
			$this->__get( $name );
			return true;
		}

		return $this->$name !== null;
	}

	/**
	 * Handles properties lazy-load.
	 */
	public function __get( $name ) {
		if( 'answers' === $name ){
			isset( $this->answers ) || $this->set_answers();
			return $this->answers;
		}

		if( 'voting_blocked' === $name || 'blockVoting' === $name ){
			isset( $this->voting_blocked ) || $this->set_voting_blocked();
			return $this->voting_blocked;
		}

		if( 'voted_for' === $name || 'votedFor' === $name ){
			isset( $this->voted_for ) || $this->set_voted_data();
			return $this->voted_for;
		}

		if( 'has_voted' === $name ){
			isset( $this->has_voted ) || $this->set_voted_data();
			return $this->has_voted;
		}

		if( 'blockForVisitor' === $name ){
			return $this->blocked_by_not_logged;
		}

		return null;
	}

	public function __set( $name, $value ) {
		if( 'voting_blocked' === $name || 'blockVoting' === $name ){
			$this->voting_blocked = (bool) $value;
		}
		elseif( 'voted_for' === $name || 'votedFor' === $name ){
			$this->voted_for = (string) $value;
		}
		elseif( 'has_voted' === $name ){
			$this->has_voted = (bool) $value;
		}
		else {
			throw new \RuntimeException( __CLASS__ . " class prohibits setting dynamic properties. You are trying to set `$name`." );
		}
	}

	/**
	 * @param object|int $poll_id  Poll ID to get. OR poll object from DB.
	 */
	public function __construct( $poll_id ) {
		if( ! $poll_id ){
			return;
		}

		is_object( $poll_id ) && $this->dbdata = $poll_id;
		is_numeric( $poll_id ) && $this->dbdata = self::get_db_data( $poll_id );
		if( empty( $this->dbdata->id ) ){
			return;
		}

		$this->id            = (int) $this->dbdata->id;
		$this->question      = (string) $this->dbdata->question;
		$this->added         = (int) $this->dbdata->added;
		$this->added_user    = (int) $this->dbdata->added_user;
		$this->end           = (int) $this->dbdata->end;
		$this->users_voted   = (int) $this->dbdata->users_voted;
		$this->democratic    = (bool) ( options()->democracy_off ? false : $this->dbdata->democratic );
		$this->active        = (bool) $this->dbdata->active;
		$this->open          = (bool) $this->dbdata->open;
		$this->multiple      = (int) $this->dbdata->multiple;
		$this->forusers      = (bool) $this->dbdata->forusers;
		$this->revote        = (bool) ( options()->revote_off ? false : $this->dbdata->revote );
		$this->show_results  = (bool) $this->dbdata->show_results;
		$this->answers_order = (string) $this->dbdata->answers_order;
		$this->in_posts      = (string) $this->dbdata->in_posts;
		$this->note          = $this->dbdata->note;

		$this->renderer = new Poll_Renderer( $this ); // after DB data is set
		$this->service  = new Poll_Service( $this );  // after DB data is set

		$this->check_poll_close();

		// block for mot logged if needed
		if( ( options()->only_for_users || $this->forusers ) && ! is_user_logged_in() ){
			$this->blocked_by_not_logged = true;
			$this->voting_blocked = true;
		}
	}

	/**
	 * Checks if the poll should be closed and updates the DB if needed.
	 * This method should be called after the poll object is created.
	 */
	private function check_poll_close(): void {
		global $wpdb;
		if( $this->open && $this->end && ( current_time( 'timestamp' ) > $this->end ) ){
			$wpdb->update( $wpdb->democracy_q, [ 'open' => 0 ], [ 'id' => $this->id ] );
			$this->open = false;
		}
	}

	/**
	 * @param int|string $poll_id Poll id to get. Specify 'rand', 'last' when you need a random or last poll.
	 *
	 * @return object|null
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

	protected function set_voting_blocked(): void {
		$blocked = ( $this->blocked_by_not_logged || ! $this->open );

		if( ! $blocked ){
			$this->set_voted_data();
			$blocked = $this->has_voted;
		}

		$this->voting_blocked = $blocked;
	}

	/**
	 * Sets the props {@see self::$has_voted} and {@see self::$voted_for}.
	 */
	protected function set_voted_data(): void {
		if( ! $this->id ){
			return;
		}

		$this->voted_for = $this->service->get_voted_for();
		$this->has_voted = (bool) $this->voted_for;
	}

	public function re_set_answers(): void {
	    $this->set_answers();
	}

	/**
	 * Gets answers from DB, sorts them in the required order, and sets it to {@see self::$answers}.
	 */
	protected function set_answers(): void {
		global $wpdb;

		$answers = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->democracy_a WHERE qid = %d", $this->id
		) );

		$is_custom_order = (bool) ( reset( $answers )->aorder ?? 0 );
		if( $is_custom_order ){
			$answers = Helpers::objects_array_sort( $answers, [ 'aorder' => 'asc' ] );
		}
		else{
			$order = $this->answers_order ?: options()->order_answers;

			if( $order === 'by_winner' || $order == 1 ){
				$answers = Helpers::objects_array_sort( $answers, [ 'votes' => 'desc' ] );
			}
			elseif( $order === 'alphabet' ){
				$answers = Helpers::objects_array_sort( $answers, [ 'answer' => 'asc' ] );
			}
			elseif( $order === 'mix' ){
				shuffle( $answers );
			}
			elseif( $order === 'by_id' ){}
		}

		$answers = array_map( static fn( $data ) => new Poll_Answer( $data ), $answers );

		/**
		 * Allows to modify the answers before they are set in the poll object.
		 *
		 * @param Poll_Answer[] $answers The answers to be set for the poll.
		 * @param DemPoll       $poll    The poll object itself.
		 */
		$this->answers = apply_filters( 'dem_set_answers', $answers, $this );
	}

}
