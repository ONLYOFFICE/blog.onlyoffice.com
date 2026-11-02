<?php

namespace DemocracyPoll;

class Poll_Ajax {

	public string $ajax_url;

	private string $action;
	private int    $poll_id;
	private string $answer_ids;
	private string $fingerprint;

	public function __construct() {
		$this->ajax_url = admin_url( 'admin-ajax.php' );
	}

	public function init(): void {
		// AJAX requests cannot be registered in frontend_init because they run under is_admin().
		add_action( 'wp_ajax_dem_ajax', [ $this, 'ajax_request_handler' ] );
		add_action( 'wp_ajax_nopriv_dem_ajax', [ $this, 'ajax_request_handler' ] );
	}

	public function ajax_request_handler(): void {
		$this->set_request_vars();

		if( ! $this->action ){
			wp_die( 'error: invalid `act` parameter' );
		}

		if( ! $this->poll_id ){
			wp_die( 'error: invalid `pid` parameter' );
		}

		$actions = [
			'vote'        => 'action__vote',
			'delVoted'    => 'action__delete_vote',
			'viewResults' => 'action__view_results',
			'view'        => 'action__view_results', // legacy
			'voteScreen'  => 'action__vote_screen',
			'vote_screen' => 'action__vote_screen', // legacy
			'getVotedIds' => 'action__get_voted_ids',
		];

		$method = $actions[ $this->action ] ?? null;
		if( ! $method ){
			wp_die( 'error: unknown action' );
		}

		/**
		 * @see self::action__vote()
		 * @see self::action__delete_vote()
		 * @see self::action__view_results()
		 * @see self::action__vote_screen()
		 * @see self::action__get_voted_ids()
		 */
		$response = $this->$method();

		wp_send_json( $response );
	}

	/**
	 * Does a preliminary sanitization of the passed request variables.
	 */
	private function set_request_vars(): void {
		$this->action = sanitize_text_field( $_POST['dem_act'] ?? '' );
		$this->poll_id = (int) ( $_POST['dem_pid'] ?? 0 );
		$this->answer_ids = wp_unslash( $_POST['answer_ids'] ?? '' );
		$this->fingerprint = sanitize_text_field( $_POST['fingerprint'] ?? '' );
	}

	/** Mockable factory method */
	protected function create_poll(): Poll {
		return new Poll( $this->poll_id );
	}

	/** Mockable factory method */
	protected function create_renderer( Poll $poll ): Poll_Renderer {
		return container()->make( Poll_Renderer::class, [ 'poll' => $poll ] ); /** @see Poll_Renderer::__construct() */
	}

	/** Mockable factory method */
	protected function create_voting_service( Poll $poll ): Poll_Voting {
		return container()->make( Poll_Voting::class, [ 'poll' => $poll ] ); /** @see Poll_Voting::__construct() */
	}

	/**
	 * Vote and display results.
	 */
	private function action__vote(): array {
		$poll = $this->create_poll();
		$render = $this->create_renderer( $poll );
		$voting = $this->create_voting_service( $poll );

		$voted = $voting->vote( $this->answer_ids, $this->fingerprint );

		if( is_wp_error( $voted ) ){
			return $this->response(
				$render->get_vote_screen(),
				'error',
				$voted->get_error_message()
			);
		}

		$screen_html = $render->not_show_results
			? $render->get_vote_screen()
			: $render->get_result_screen();

		return $this->response( $screen_html );
	}

	// delete results
	private function action__delete_vote(): array {
		$poll = $this->create_poll();
		$render = $this->create_renderer( $poll );
		$voting = $this->create_voting_service( $poll );

		$voting->delete_vote( $this->fingerprint );

		return $this->response( $render->get_vote_screen() );
	}

	// view results
	private function action__view_results(): array {
		$poll = $this->create_poll();
		$render = $this->create_renderer( $poll );

		$screen_html = $render->not_show_results
			? $render->get_vote_screen()
			: $render->get_result_screen();

		return $this->response( $screen_html );
	}

	// back to voting
	private function action__vote_screen(): array {
		$poll = $this->create_poll();
		$render = $this->create_renderer( $poll );

		return $this->response( $render->get_vote_screen() );
	}

	// request is only made if cookies are not set - 'checkAnswDone' not done
	private function action__get_voted_ids(): array {
		$poll = $this->create_poll();
		$render = $this->create_renderer( $poll );

		$ustate = $poll->user_state;
		$ustate->poll_logs->set_fingerprint( $this->fingerprint );
		if( $ustate->voted_for ){
			$ustate->set_vote_cookie();
			$screen_html = $render->not_show_results
				? $render->get_vote_screen()
				: $render->get_result_screen();

			return $this->response(
				$screen_html,
				'already_voted',
				Poll_Renderer::already_voted_notice_message(),
				$ustate->voted_for
			);
		}

		if( $ustate->blocked_by_not_logged ){
			return $this->response(
				'',
				'login_required',
				$render->registered_only_notice_html()
			);
		}

		// Cache a missing vote for half a day to avoid repeating this check.
		$ustate->set_not_voted_cookie();

		// Replace results shown from a stale vote cookie with the actual vote screen.
		return $this->response( $render->get_vote_screen() );
	}

	private function response(
		string $screen_html = '',
		string $notice_status = '',
		string $notice_html = '',
		string $voted_for = ''
	): array {
		return [
			'screen_html' => $screen_html,
			'notice'      => $notice_status
				? [
					'status' => $notice_status,
					'html'   => wp_kses_post( $notice_html ),
				]
				: null,
			'voted_for'   => $voted_for,
		];
	}

}
