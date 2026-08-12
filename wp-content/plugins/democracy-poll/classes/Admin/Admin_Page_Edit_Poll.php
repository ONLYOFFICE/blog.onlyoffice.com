<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Kses;
use DemocracyPoll\Support\Messages;
use DemocracyPoll\Options;
use DemocracyPoll\Poll;
use DemocracyPoll\Poll_Storage;
use DemocracyPoll\Poll_Utils;
use DemocracyPoll\Plugin;
use function DemocracyPoll\container;

class Admin_Page_Edit_Poll implements Admin_Subpage_Interface {

	private int $poll_id = 0;

	private ?Poll $poll = null;

	private Admin_Page $admpage;
	private Messages $messages;
	private Plugin $plugin;
	private Options $options;

	public function __construct(
		Admin_Page $admin_page,
		Messages $messages,
		Plugin $plugin,
		Options $options
	) {
		$this->admpage = $admin_page;
		$this->messages = $messages;
		$this->plugin = $plugin;
		$this->options = $options;
	}

	public function set_poll_id( int $poll_id ): void {
		$this->poll_id = $poll_id;
	}

	public function load(): void {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	public function request_handler(): void {
		if( ( $_GET['msg'] ?? '' ) === 'created' ){
			$this->messages->add_ok( __( 'New Poll Added', 'democracy-poll' ) );
		}

		if( ! Admin_Page::check_nonce() ){
			return;
		}

		$is_update = isset( $_POST['dmc_update_poll'] );
		$is_create = isset( $_POST['dmc_create_poll'] );
		$poll_id = (int) ( $_POST['dmc_update_poll'] ?? $_POST['dmc_create_poll'] ?? 0 );

		if( $is_update ){
			if( ! $poll_id ){
				$this->messages->add_error( 'Poll ID to be edited not set' );
				return;
			}

			if( ! Poll_Utils::cuser_can_edit_poll( $poll_id ) ){
				$this->messages->add_error( 'Low cap to update poll' );
				return;
			}

			$this->insert_poll_handler( $poll_id );
		}
		elseif( $is_create ){
			if( ! $this->plugin->admin_access ){
				$this->messages->add_error( 'Low cap to create poll' );
				return;
			}

			$this->insert_poll_handler( 0 );
		}
	}

	public function render(): void {
		// no access
		if( $this->poll_id && ! Poll_Utils::cuser_can_edit_poll( $this->poll_id ) ){
			echo '<div class="notice notice-error"><p>Sorry, you are not allowed to access this page</p></div>';
			return;
		}

		$this->poll = new Poll( $this->poll_id );

		echo $this->admpage->subpages_menu();

		require __DIR__ . '/tpl/edit-poll.php';
	}

	public function insert_poll_handler( int $poll_id = 0 ): void {
		$data = [];

		// collect all fields which start with 'dmc_'
		foreach( (array) $_POST as $key => $val ){
			/**
			 * dmc_qid
			 * dmc_added
			 * dmc_added_user
			 * dmc_open
			 * dmc_multiple
			 * dmc_users_voted
			 * dmc_question
			 * dmc_old_answers
			 * dmc_new_answers
			 * dmc_democratic
			 * dmc_active
			 * dmc_end
			 * dmc_revote
			 * dmc_forusers
			 * dmc_show_results
			 * dmc_answers_order
			 * dmc_note
			 */
			if( str_starts_with( $key, 'dmc_' ) ){
				$data[ substr( $key, 4 ) ] = $val;
			}
		}

		$data = wp_unslash( $data );
		$data['qid'] = $poll_id;

		$this->insert_poll( $data );
	}

	/**
	 * Add or Update poll. Expects unslashed data.
	 *
	 * @param array $data  Data of added poll. If set 'qid' key poll wil be updated.
	 *
	 * @return bool True when added updated, False otherwise.
	 */
	public function insert_poll( array $insert_data ): bool {
		global $wpdb;

		$poll_id = (int) ( $insert_data['qid'] ?? 0 );
		$update = (bool) $poll_id;

		// sanitize
		$data = $this->sanitize_poll_data( $insert_data );

		if( ! $data['question'] ){
			$this->messages->add_warn( 'error: question not set' );

			return false;
		}

		/// answers
		$old_answers = (array) ( $data['old_answers'] ?? [] );
		$new_answers = array_filter( (array) ( $data['new_answers'] ?? [] ) );

		// add data if insert new poll
		if( ! $update ){
			$data['added'] = current_time( 'timestamp' );
			$data['added_user'] = get_current_user_id();
			$data['open'] = 1; // poll is open by default
		}

		// Remove invalid for the table fields
		$q_fields = wp_list_pluck( $wpdb->get_results( "SHOW COLUMNS FROM $wpdb->democracy_q" ), 'Field' );
		$q_data = array_intersect_key( $data, array_flip( $q_fields ) );

		/**
		 * Allows to modify the poll data before insert or update.
		 *
		 * @param array $q_data        The poll data to be inserted or updated.
		 * @param array $old_answers   The old answers of the poll.
		 * @param array $new_answers   The new answers of the poll.
		 * @param bool  $update        Whether the poll is being updated or created.
		 */
		do_action_ref_array( 'dem_before_insert_quest_data', [ & $q_data, & $old_answers, & $new_answers, $update ] );

		// UPDATE POLL
		if( $update ){
			$wpdb->update( $wpdb->democracy_q, $q_data, [ 'id' => $poll_id ] );

			if( 'upadate answers' ){ // @phpstan-ignore-line
				$ids = [];

				// Update existing answers.
				foreach( $old_answers as $aid => $anws ){
					$answ_row = $wpdb->get_row( "SELECT * FROM $wpdb->democracy_a WHERE aid = " . (int) $aid );

					// Remove the NEW marker.
					$added_by = Admin_Page_Logs::is_new_answer( $answ_row )
						? str_replace( '-new', '', $answ_row->added_by )
						: $answ_row->added_by;

					$order = $anws['aorder'];

					$wpdb->update(
						$wpdb->democracy_a,
						[
							'answer'   => $anws['answer'],
							'votes'    => $anws['votes'],
							'aorder'   => $order,
							'added_by' => $added_by,
						],
						[ 'qid' => $poll_id, 'aid' => $aid ]
					);

					// Collect the remaining IDs so they are not deleted.
					$ids[] = $aid;
					$max_order_num = isset( $max_order_num ) ? ( $max_order_num < $order ? $order : $max_order_num ) : $order;
				}

				if( 'Delete removed answers that exist in the database but are absent from the request' ){ // @phpstan-ignore-line
					$ids = array_map( 'absint', $ids );
					$AND_NOT_IN = $ids ? sprintf( "AND aid NOT IN (" . implode( ',', $ids ) . ")" ) : '';
					$del_ids = $wpdb->get_col(
						"SELECT aid FROM $wpdb->democracy_a WHERE qid = $poll_id $AND_NOT_IN"
					);

					if( $del_ids ){
						// delete answers
						$deleted = $wpdb->query( "DELETE FROM $wpdb->democracy_a WHERE aid IN (" . implode( ',', $del_ids ) . ")" );

						if( 'delete answers logs' ){ // @phpstan-ignore-line
							// delete logs
							$user_voted_minus = $wpdb->query(
								"DELETE FROM $wpdb->democracy_log WHERE qid = $poll_id AND aids IN (" . implode( ',', $del_ids ) . ")"
							);

							// Update the users_voted value in the database.
							if( $user_voted_minus ){
								$wpdb->query( Admin_Page_Logs::users_voted_minus_sql( $user_voted_minus, $poll_id ) );
							}

							// Update multiple-answer logs containing values such as '321,654'.
							$up_logs = $wpdb->get_results(
								"SELECT logid, aids FROM $wpdb->democracy_log
									WHERE qid = $poll_id AND aids RLIKE '(" . implode( '|', $del_ids ) . ")'"
							);

							foreach( $up_logs as $log ){
								$_ids_patt = implode( '|', $del_ids ); // pattern part
								$new_aids = preg_replace( "~^(?:$_ids_patt),|,(?:$_ids_patt)(?=,)|,(?:$_ids_patt)\$~", '', $log->aids );
								$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->democracy_log SET aids = %s WHERE logid = $log->logid", $new_aids ) );
							}
						}

						if( $deleted ){
							/**
							 * Allows to perform actions after answers are deleted.
							 *
							 * @param array $del_ids  The IDs of the deleted answers.
							 * @param int   $poll_id  The ID of the poll from which answers were deleted.
							 */
							do_action( 'dem_answers_deleted', $del_ids, $poll_id );
						}
					}
				}

				// Add new answers.
				foreach( $new_answers as $anws ){
					$anws = trim( $anws );

					if( $anws ){
						$wpdb->insert( $wpdb->democracy_a, [
							'answer' => $anws,
							'aorder' => ( $max_order_num ?? 0 ) ? $max_order_num++ : 0,
							'qid'    => $poll_id,
						] );
					}
				}
			}

			$this->messages->add_ok( __( 'Poll Updated', 'democracy-poll' ) );

			// collect answers users votes count
			// Update questions.users_voted after the logs because its value depends on them.
			if( 1 ){ // @phpstan-ignore-line
				$users_voted = 0;
				// Calculate the value from the logs.
				if( $data['multiple'] && ! $data['users_voted'] ){
					$users_voted = $wpdb->get_var( "SELECT count(*) FROM $wpdb->democracy_log WHERE qid = " . (int) $poll_id );
				}
				// Equal to the number of votes.
				if( ! $data['multiple'] ){
					$users_voted = $wpdb->get_var( "SELECT SUM(votes) FROM $wpdb->democracy_a WHERE qid = " . (int) $poll_id );
				}
				//$users_voted = array_sum( wp_list_pluck($old_answers, 'votes') );

				if( $users_voted ){
					$wpdb->update( $wpdb->democracy_q, [ 'users_voted' => $users_voted ], [ 'id' => $poll_id ] );
				}
			}
		}
		// ADD POLL
		else{
			$wpdb->insert( $wpdb->democracy_q, $q_data );

			if( ! $poll_id = $wpdb->insert_id ){
				$this->messages->add_error( 'error: sql error when adding poll data' );

				return false;
			}

			foreach( $new_answers as $answer ){
				$answer = trim( $answer );

				if( ! empty( $answer ) ){
					$wpdb->insert( $wpdb->democracy_a, [ 'answer' => $answer, 'qid' => $poll_id ] );
				}
			}

			wp_redirect( add_query_arg( [ 'msg' => 'created' ], Poll_Utils::edit_poll_url( $poll_id ) ) );
		}

		/**
		 * Allows performing actions after a poll is inserted or updated.
		 *
		 * @param int  $poll_id The ID of the poll that was inserted or updated.
		 * @param bool $update  Whether the poll was updated (true) or created (false).
		 */
		do_action( 'dem_poll_inserted', $poll_id, $update );

		return true;
	}

	/**
	 * Sanitize all poll fields before save in db.
	 */
	public function sanitize_poll_data( array $data ): array {
		$original_data = $data;

		foreach( $data as $key => & $val ){
			if( is_string( $val ) ){
				$val = trim( $val );
			}

			// valid tags
			if( $key === 'question' || $key === 'note' ){
				$val = Kses::kses_html( $val );
			}
			// date
			elseif( $key === 'end' || $key === 'added' ){
				if( preg_match( '~\d{1,2}-\d{1,2}-\d{4}~', $val ) ){
					$val = strtotime( $val );
				}
				else{
					$val = 0;
				}
			}
			// fix multiple
			elseif( $key === 'multiple' && $val == 1 ){
				$val = 2;
			}
			// numbers
			elseif( in_array( $key, [ 'qid', 'democratic', 'active', 'multiple', 'forusers', 'revote' ] ) ){
				$val = (int) $val;
			}
			// answers
			elseif( $key === 'old_answers' || $key === 'new_answers' ){
				if( is_string( $val ) ){
					$val = Kses::sanitize_answer_data( $val );
				}
				else{
					foreach( $val as & $_val ){
						$_val = Kses::sanitize_answer_data( $_val );
					}
					unset( $_val );
				}
			}
			// remove tags
			else{
				$val = wp_kses( $val, 'strip' );
			}
		}
		unset( $val );

		/**
		 * Allows to modify the poll data during sanitization (typically before inserting to DB).
		 *
		 * @param array $data          The sanitized poll data.
		 * @param array $original_data The original data before sanitization.
		 */
		return (array) apply_filters( 'demadmin_sanitize_poll_data', $data, $original_data );
	}

	public static function shortcode_html( $poll_id ): string {
		if( ! $poll_id ){
			return '';
		}

		return '<span style="cursor:pointer; padding:2px 4px; background:#fff;"
		onclick="var sel = window.getSelection(), range = document.createRange(); range.selectNodeContents(this); sel.removeAllRanges(); sel.addRange(range); document.execCommand(\'copy\');">[democracy id="' . $poll_id . '"]</span>';
	}

	/**
	 * Displays poll activation/deactivation button.
	 */
	public static function activate_button( Poll $poll, $reverse = false, $size = 'big' ): string {
		if( $poll->active ){
			$url = esc_url( Admin_Page::add_nonce( add_query_arg( [ 'dmc_deactivate_poll' => $poll->id, 'dmc_activate_poll' => null, ] ) ) );
			$title = __( 'Deactivate', 'democracy-poll' );
			$icon = $reverse ? 'dashicons-controls-play' : 'dashicons-controls-pause';
		}
		else{
			$url = esc_url( Admin_Page::add_nonce( add_query_arg( [ 'dmc_deactivate_poll' => null, 'dmc_activate_poll' => $poll->id, ] ) ) );
			$title = __( 'Activate', 'democracy-poll' );
			$icon = $reverse ? 'dashicons-controls-pause' : 'dashicons-controls-play';
		}

		return sprintf(
			'<a class="button %s" href="%s" title="%s"><span class="dashicons %s"></span></a>',
			( $size === 'small' ? 'button-small' : '' ), esc_url( $url ), esc_html( $title ), $icon
		);
	}

	/**
	 * Displays poll open/close button.
	 */
	public static function open_button( $poll, $reverse = false, $size = 'big' ): string {
		if( $poll->open ){
			$url = esc_url( Admin_Page::add_nonce( add_query_arg( [ 'dmc_close_poll' => $poll->id, 'dmc_open_poll' => null ] ) ) );
			$title = __( 'Close voting', 'democracy-poll' );
			$icon = $reverse ? 'dashicons-yes' : 'dashicons-no';
		}
		else{
			$url = esc_url( Admin_Page::add_nonce( add_query_arg( [ 'dmc_close_poll' => null, 'dmc_open_poll' => $poll->id ] ) ) );
			$title = __( 'Open voting', 'democracy-poll' );
			$icon = $reverse ? 'dashicons-no' : 'dashicons-yes';
		}

		return sprintf(
			'<a class="button %s" href="%s" title="%s"><span class="dashicons %s"></span></a>',
			( $size === 'small' ? 'button-small' : '' ), esc_url( $url ), esc_html( $title ), $icon
		);
	}

	public static function delete_button( $poll ): string {
		return sprintf(
			' <a href="%s" class="button" onclick="return confirm(\'%s\');" title="%s"><span class="dashicons dashicons-trash"></span></a>',
			Admin_Page::add_nonce( add_query_arg( [ 'delete_poll' => $poll->id ], container()->get( Plugin::class )->admin_page_url ) ),
			__( 'Are you sure?', 'democracy-poll' ),
			__( 'Delete', 'democracy-poll' )
		);
	}

	public static function delete_poll( $poll_id ): void {
		global $wpdb;

		$poll_id = (int) $poll_id;
		if( ! $poll_id ){
			return;
		}

		$wpdb->delete( $wpdb->democracy_q, [ 'id' => $poll_id ] );
		$wpdb->delete( $wpdb->democracy_a, [ 'qid' => $poll_id ] );
		$wpdb->delete( $wpdb->democracy_log, [ 'qid' => $poll_id ] );

		container()->get( Messages::class )->add_ok( __( 'Poll Deleted', 'democracy-poll' ) . ": $poll_id" );
	}

	public static function open_poll( int $poll_id ): bool {
		return self::_poll_opening( $poll_id, 'open' );
	}

	public static function close_poll( int $poll_id ): bool {
		return self::_poll_opening( $poll_id, 'close' );
	}

	public static function activate_poll( int $poll_id ): bool {
		return self::_poll_activation( $poll_id, 'activate' );
	}

	public static function deactivate_poll( int $poll_id ): bool {
		return self::_poll_activation( $poll_id, 'deactivate' );
	}

	/**
	 * Closes/opens voting
	 *
	 * @param int    $poll_id  Poll ID
	 * @param string $action   What to do, open or close voting?
	 */
	private static function _poll_opening( int $poll_id, string $action ): bool {
		global $wpdb;

		$poll = Poll_Storage::get_db_data( $poll_id );
		if( ! $poll ){
			return false;
		}

		$new_data = [ 'open' => ( $action === 'open' ) ? 1 : 0 ];

		// Remove the end date when voting is opened.
		if( $action === 'open' ){
			$new_data['end'] = 0;
		}
		// Set the end date and deactivate the poll when voting is closed.
		else{
			$new_data['end'] = current_time( 'timestamp' ) - 10;
			self::deactivate_poll( $poll_id );
		}

		$done = $wpdb->update( $wpdb->democracy_q, $new_data, [ 'id' => $poll->id ] );

		if( $done ){
			container()->get( Messages::class )->add_ok( ( $action === 'open' )
				? __( 'Poll Opened', 'democracy-poll' )
				: __( 'Poll Closed', 'democracy-poll' )
			);
		}

		return (bool) $done;
	}

	/**
	 * Activate and deactivate a specified poll.
	 *
	 * @param int  $poll_id     Poll ID.
	 * @param string $action    One of: activate, deactivate
	 */
	private static function _poll_activation( int $poll_id, string $action ): bool {
		global $wpdb;

		$poll = Poll_Storage::get_db_data( $poll_id );
		if( ! $poll ){
			return false;
		}

		$activate = ( $action === 'activate' );

		if( ! $poll->open && $activate ){
			container()->get( Messages::class )->add_error( __( 'You can not activate closed poll...', 'democracy-poll' ) );

			return false;
		}

		$done = $wpdb->update( $wpdb->democracy_q, [ 'active' => $activate ? 1 : 0 ], [ 'id' => $poll->id ] );

		if( $done ){
			container()->get( Messages::class )->add_ok( $activate
				? __( 'Poll Activated', 'democracy-poll' )
				: __( 'Poll Deactivated', 'democracy-poll' )
			);
		}

		return (bool) $done;
	}

}
