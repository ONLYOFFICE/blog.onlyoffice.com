<?php

namespace DemocracyPoll;

class Poll_Answer {

	/** Unique ID of the answer. */
	public string $aid;

	/** Unique ID of the question this answer belongs to. */
	public string $qid;

	/** The answer text. */
	public string $answer;

	/** The Number of votes this answer has received. */
	public int $votes;

	/**
	 * The order of the answer in the list of answers.
	 * If this field is not falsy, then the answers have custom order.
	 */
	public int $aorder;

	/** The user who added this answer. */
	public string $added_by;

	public function __construct( $data ) {
		$data = (array) $data;

		$this->aid      = (string) $data['aid'];
		$this->qid      = (string) $data['qid'];
		$this->answer   = (string) $data['answer'];
		$this->votes    = (int) $data['votes'];
		$this->aorder   = (int) $data['aorder'];
		$this->added_by = (string) $data['added_by'];
	}

}
