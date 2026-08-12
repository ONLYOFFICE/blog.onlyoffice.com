<?php

namespace DemocracyPoll;

use DemocracyPoll\Support\Helpers;

/**
 * @property-read Poll_Answer[] $answers Poll Answers.
 */
class Poll extends DemPoll_Legacy {

	public Poll_User_State $user_state; /* readonly */

	/**
	 * Lazy loaded property.
	 * @see self::set_answers()
	 * @var Poll_Answer[]
	 */
	private array $answers;

	/// DB Fields

	/** Poll ID (DB Field) */
	public int $id = 0;

	/** Poll title (DB Field) */
	public string $question = '';

	/** Added UNIX timestamp (DB Field) */
	public int $added = 0;

	/** End UNIX timestamp (DB Field) */
	public int $end = 0;

	/** User ID (DB Field) */
	public int $added_user = 0;

	/** How many users voted for this poll (DB Field) */
	public int $users_voted = 0;

	/** Is this poll democratic? (DB Field) */
	public bool $democratic = true;

	/** Is this poll active? (DB Field) */
	public bool $active = true;

	/** Is this poll open for voting? (DB Field) */
	public bool $open = true;

	/** How many answers may be selected. (DB Field) */
	public int $multiple = 0;

	/** For logged users only (DB Field) */
	public bool $forusers = false;

	/** Allow to revote (DB Field) */
	public bool $revote = true;

	/** Show results after voting (DB Field) */
	public bool $show_results = true;

	/** Answers order: 'by_winner', 'by_id', 'alphabet', 'mix'. {@see Helpers::allowed_answers_orders()} (DB Field) */
	public string $answers_order = '';

	/** Comma separated posts_ids. Eg: '16865,16892' (DB Field) */
	public string $in_posts = '';

	/** Additional poll notes (DB Field) */
	public string $note = '';

	public function __isset( $name ) {
		if( 'answers' === $name ){
			$this->__get( $name );
			return true;
		}

		if( parent::__isset( $name ) ){
			return true;
		}

		return property_exists( $this, $name ) && $this->$name !== null;
	}

	/**
	 * Handles properties lazy-load.
	 */
	public function __get( $name ) {
		if( 'answers' === $name ){ /** @see self::$answers */
			isset( $this->answers ) || $this->set_answers();
			return $this->answers;
		}

		return parent::__get( $name );
	}

	/**
	 * @param object|int $poll_id  Poll ID to get. OR poll object from DB.
	 */
	public function __construct( $poll_id ) {
		$this->user_state = new Poll_User_State( $this );

		if( ! $poll_id ){
			return;
		}

		$dbdata = is_object( $poll_id ) ? $poll_id : Poll_Storage::get_db_data( $poll_id );
		if( empty( $dbdata->id ) ){
			return;
		}

		$options = container()->get( Options::class );

		$this->id            = (int) $dbdata->id;
		$this->question      = (string) $dbdata->question;
		$this->added         = (int) $dbdata->added;
		$this->added_user    = (int) $dbdata->added_user;
		$this->end           = (int) $dbdata->end;
		$this->users_voted   = (int) $dbdata->users_voted;
		$this->democratic    = (bool) ( $options->democracy_off ? false : $dbdata->democratic );
		$this->active        = (bool) $dbdata->active;
		$this->open          = (bool) $dbdata->open;
		$this->multiple      = (int) $dbdata->multiple;
		$this->forusers      = (bool) $dbdata->forusers;
		$this->revote        = (bool) ( ! $options->revote_off && $dbdata->revote );
		$this->show_results  = (bool) $dbdata->show_results;
		$this->answers_order = (string) $dbdata->answers_order;
		$this->in_posts      = (string) $dbdata->in_posts;
		$this->note          = $dbdata->note;

		Poll_Storage::close_if_expired( $this ); // TODO: move out from constructor
	}

	/**
	 * Gets {@see self::$answers} from DB data.
	 */
	public function set_answers(): void {
		$this->answers = Poll_Storage::get_answers( $this );
	}

}
