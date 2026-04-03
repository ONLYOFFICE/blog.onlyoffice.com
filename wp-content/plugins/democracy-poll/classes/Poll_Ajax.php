<?php

namespace DemocracyPoll;

class Poll_Ajax {

	public string $ajax_url;

	public function __construct(){
		$this->ajax_url = admin_url( 'admin-ajax.php' );
	}

	public function init(): void {
		// ajax request во frontend_init нельзя, потому что срабатывает только как is_admin()
		add_action( 'wp_ajax_dem_ajax', [ $this, 'ajax_request_handler' ] );
		add_action( 'wp_ajax_nopriv_dem_ajax', [ $this, 'ajax_request_handler' ] );

		// to work without AJAX
		if(
			isset( $_POST['dem_act'] )
		    && ( ! isset( $_POST['action'] ) || 'dem_ajax' !== $_POST['action'] )
		){
			add_action( 'init', [ $this, 'not_ajax_request_handler' ], 99 );
		}
	}

	/**
	 * Does a preliminary sanitization of the passed request variables.
	 */
	public function sanitize_request_vars(): array {
		return [
			'act'  => sanitize_text_field( $_POST['dem_act'] ?? '' ),
			'pid'  => (int) ( $_POST['dem_pid'] ?? 0 ),
			'aids' => wp_unslash( $_POST['answer_ids'] ?? '' ),
		];
	}

	public function ajax_request_handler(): void {
		$vars = (object) $this->sanitize_request_vars();

		if( ! $vars->act ){
			wp_die( 'error: no parameters have been sent or it is unavailable' );
		}

		if( ! $vars->pid ){
			wp_die( 'error: unknown poll id' );
		}

		$poll = new \DemPoll( $vars->pid );
		$render = $poll->renderer;
		$service = $poll->service;

		// vote and display results
		if( 'vote' === $vars->act && $vars->aids ){
			$voted = $service->vote( $vars->aids );

			if( is_wp_error( $voted ) ){
				echo $render::voted_notice_html( $voted->get_error_message() );
				echo $render->get_vote_screen();
			}
			elseif( $render->not_show_results ){
				echo $render->get_vote_screen();
			}
			else{
				echo $render->get_result_screen();
			}
		}
		// delete results
		elseif( 'delVoted' === $vars->act ){
			$service->delete_vote();
			echo $render->get_vote_screen();
		}
		// view results
		elseif( 'view' === $vars->act ){
			if( $render->not_show_results ){
				echo $render->get_vote_screen();
			}
			else{
				echo $render->get_result_screen();
			}
		}
		// back to voting
		elseif( 'vote_screen' === $vars->act ){
			echo $render->get_vote_screen();
		}
		/** Get {@see \DemPoll::$voted_for} value */
		elseif( 'getVotedIds' === $vars->act ){
			if( $poll->voted_for ){
				$service->set_cookie(); // request is only made if cookies are not set
				echo $poll->voted_for;
			}
			elseif( $poll->blocked_by_not_logged ){
				echo 'blocked_because_not_logged_note'; // to display a note
			}
			else{
				// If not voted, set a cookie for half a day to don't do this check every time.
				$service->set_cookie( 'notVote', ( time() + ( DAY_IN_SECONDS / 2 ) ) );
			}
		}

		wp_die();
	}

	/**
	 * To work without AJAX.
	 */
	public function not_ajax_request_handler(): void {
		$vars = (object) $this->sanitize_request_vars();

		if( ! $vars->act || ! $vars->pid || ! isset( $_SERVER['HTTP_REFERER'] ) ){
			return;
		}

		$poll = new \DemPoll( $vars->pid );
		$service = $poll->service;

		if( 'vote' === $vars->act && $vars->aids ){
			$service->vote( $vars->aids );
			wp_safe_redirect( remove_query_arg( [ 'dem_act', 'dem_pid' ], $_SERVER['HTTP_REFERER'] ) );

			exit;
		}

		if( 'delVoted' === $vars->act ){
			$service->delete_vote();
			wp_safe_redirect( remove_query_arg( [ 'dem_act', 'dem_pid' ], $_SERVER['HTTP_REFERER'] ) );

			exit;
		}
	}

}
