<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Kses;
use DemocracyPoll\Support\Messages;
use DemocracyPoll\Options;
use DemocracyPoll\Poll_Storage;
use DemocracyPoll\Poll_Utils;
use DemocracyPoll\Poll;
use WP_List_Table;

class List_Table_Logs extends WP_List_Table {

	private static array $cache;

	public int $poll_id;

	private Admin_Page_Logs $logs_page;
	private Messages $messages;
	private Options $options;

	public function __construct(
		Admin_Page_Logs $logs_page,
		Messages $messages,
		Options $options
	) {
		$this->logs_page = $logs_page;
		$this->messages = $messages;
		$this->options = $options;

		parent::__construct( [
			'singular' => 'demlog',
			'plural'   => 'demlogs',
			'ajax'     => false,
		] );

		$this->bulk_action_handler();

		add_screen_option( 'per_page', [
			'label'   => __( 'Show on page', 'democracy-poll' ),
			'default' => 20,
			'option'  => 'dem_logs_per_page',
		] );

		$this->poll_id = (int) ( $_GET['poll'] ?? 0 );

		$this->prepare_items();
	}

	private function bulk_action_handler(): void {
		$nonce = $_POST['_wpnonce'] ?? '';
		if( ! $nonce || ! ( $action = $this->current_action() ) ){
			return;
		}

		if( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ){
			wp_die( 'nonce error' );
		}

		if( ! $log_ids = array_filter( array_map( 'intval', $_POST['logids'] ) ) ){
			$this->messages->add_error( __( 'Nothing was selected.', 'democracy-poll' ) );

			return;
		}

		// delete
		if( 'delete_only_logs' === $action ){
			$this->logs_page->del_only_logs( $log_ids );
		}

		// delete (with votes)
		if( 'delete_logs_votes' === $action ){
			$this->logs_page->del_logs_and_votes( $log_ids );
		}
	}

	public function prepare_items(): void {
		global $wpdb;

		$per_page = get_user_meta( get_current_user_id(), get_current_screen()->get_option( 'per_page', 'option' ), true ) ?: 20;

		$filter = ( $_GET['filter'] ?? '' );
		$userid = (int) ( $_GET['userid'] ?? 0 );
		$ip = ( $_GET['ip'] ?? '' );

		// build a query

		$WHERE = 'WHERE 1';
		if( $this->poll_id ){
			$WHERE .=  $wpdb->prepare( ' AND qid = %d', $this->poll_id );
		}
		if( $userid ){
			$WHERE .=  $wpdb->prepare( ' AND userid = %d', $userid );
		}
		if( $ip ){
			$WHERE .= $wpdb->prepare( ' AND ip = %s', $ip );
		}

		// new answers
		if( 'new_answers' === $filter ){
			$aqids = $wpdb->get_results( "SELECT DISTINCT aid, qid FROM $wpdb->democracy_a WHERE added_by LIKE '%-new'" );
			if( $aqids ){
				$qid_IN = implode( ',', wp_list_pluck( $aqids, 'qid' ) );
				$aid_OR = implode( '|', wp_list_pluck( $aqids, 'aid' ) );
				$WHERE .= " AND qid IN ($qid_IN) AND ( aids RLIKE '(^|,)($aid_OR)(,|$)' )";
			}
			else{
				$WHERE .= ' AND 0 ';
			}
		}

		// pagination
		$this->set_pagination_args( [
			'total_items' => $wpdb->get_var( "SELECT count(*) FROM $wpdb->democracy_log $WHERE" ),
			'per_page'    => $per_page,
		] );
		$cur_page = $this->get_pagenum(); // !!! after set_pagination_args()

		$OFFSET = 'LIMIT ' . ( ( $cur_page - 1 ) * $per_page . ',' . $per_page );

		$order = ( strtolower( $_GET['order'] ?? '' ) === 'asc' ) ? 'ASC' : 'DESC';
		$orderby = sanitize_key( $_GET['orderby'] ?? 'date' );
		$ORDER_BY = sprintf( "ORDER BY %s %s", $orderby, $order );

		$sql = "SELECT * FROM $wpdb->democracy_log $WHERE $ORDER_BY $OFFSET";

		$this->items = $wpdb->get_results( $sql );
	}

	public function get_columns(): array {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'ip'      => 'IP',
			'ip_info' => __( 'IP info', 'democracy-poll' ),
			'qid'     => __( 'Poll', 'democracy-poll' ),
			'aids'    => __( 'Answer', 'democracy-poll' ),
			'userid'  => __( 'User', 'democracy-poll' ),
			'date'    => __( 'Date', 'democracy-poll' ),
			'expire'  => __( 'Expire', 'democracy-poll' ),
		];

		if( $this->poll_id ){
			unset( $columns['qid'] );
		}

		return $columns;
	}

	public function get_hidden_columns(): array {
		return [];
	}

	public function get_sortable_columns(): array {
		return [
			'ip'      => [ 'ip', 'asc' ],
			'ip_info' => [ 'ip_info', 'asc' ],
			'qid'     => [ 'qid', 'desc' ],
			'userid'  => [ 'userid', 'asc' ],
			'date'    => [ 'date', 'desc' ],
		];
	}

	protected function get_bulk_actions(): array {
		return [
			'delete_only_logs'  => __( 'Delete logs only', 'democracy-poll' ),
			'delete_logs_votes' => __( 'Delete logs and votes', 'democracy-poll' ),
		];
	}

	public function table_title(): void {
		if( ! $this->poll_id ){
			return;
		}

		if( ! $poll = $this->cache( 'polls', $this->poll_id ) ){
			$poll = new Poll( $this->poll_id );
			$this->cache( 'polls', $this->poll_id, $poll );
		}

		echo strtr( '<h2>{title} {question} <a href="{url}" class="button button-small">{link_text}</a></h2>', [
			'{title}'     => __( 'Poll\'s logs:', 'democracy-poll' ),
			'{question}'  => Kses::kses_html( $poll->question ),
			'{url}'       => Poll_Utils::edit_poll_url( $this->poll_id ),
			'{link_text}' => __( 'Edit poll', 'democracy-poll' ),
		] );
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 */
	public function extra_tablenav( $which ) {
		if( $which === 'top' ){
			$newfilter = ( $_GET['filter'] ?? '' ) === 'new_answers';

			$a = '';
			if( ! $this->options->democracy_off ){
				$a = strtr( '<a class="button button-small" href="{URL}">{TITLE}</a>', [
					'{URL}' => esc_url( add_query_arg( [ 'filter' => $newfilter ? null : 'new_answers' ] ) ),
					'{TITLE}' => ( $newfilter ? ' &#215; ' : '' ) . __( 'NEW answers logs', 'democracy-poll' ),
				] );
			}

			echo <<<HTML
			<div class="alignleft actions" style="margin-top:.3em;">
				{$a}
			</div>
			HTML;
		}
	}

	/**
	 * If you specify the value, the cache will be set.
	 *
	 * @param string     $type The type of cache (e.g., 'polls', 'users', 'answs', 'flagcss').
	 * @param string|int $key  The key for the cache item.
	 * @param null|mixed $val  The value to set in the cache. If null, it will just return the cached value.
	 *
	 * @return mixed
	 */
	private function cache( string $type, $key, $val = null ) {
		$cache = & self::$cache[ $type ][ $key ];
		if( ! isset( $cache ) && $val !== null ){
			$cache = $val;
		}

		return $cache;
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param \stdClass $item The log item.
	 */
	protected function column_cb( $item ): void {
		$logid = (int) $item->logid;
		echo '<input id="cb-select-' . $logid . '" type="checkbox" name="logids[]" value="' . $logid . '" />';
	}

	protected function column_ip_info( $log ) {
		$needs_update = Admin_Page_Logs::ip_info_needs_update( $log );
		$classes = 'dem-ip-info ' . ( $needs_update ? 'dem_ip_info_pending_js' : '' );
		$html = $needs_update ? '' : Admin_Page_Logs::ip_info_html( (string) $log->ip_info );

		return sprintf(
			'<div class="%s" data-log-id="%d">%s</div>',
			esc_attr( $classes ),
			(int) $log->logid,
			$html
		);
	}

	/**
	 * Fill columns.
	 *
	 * @param \stdClass $log     The log object form DB {@see $wpdb->democracy_log} table.
	 * @param string    $column  The column name.
	 */
	protected function column_default( $log, $column ) {
		global $wpdb;

		if( 'ip' === $column ){
			return sprintf( '<a title="%s" href="%s">%s</a>',
				__( 'Search by IP', 'democracy-poll' ),
				esc_url( add_query_arg( [ 'ip' => $log->ip, 'poll' => null ] ) ),
				esc_html( $log->ip )
			);
		}

		if( 'qid' === $column ){
			if( ! $poll = $this->cache( 'polls', $log->qid ) ){
				$poll = $this->cache( 'polls', $log->qid, Poll_Storage::get_db_data( $log->qid ) );
			}

			$actions = '';
			if( Poll_Utils::cuser_can_edit_poll( $poll ) ){
				$actions = strtr( <<<'HTML'
					<div class="row-actions">
						<span class="edit"><a href="{edit_url}">{edit_text}</a> | </span>
						<span class="edit"><a href="{logs_url}">{logs_text}</a></span>
					</div>
					HTML,
					[
						'{edit_url}'  => Poll_Utils::edit_poll_url( $poll->id ),
						'{edit_text}' => __( 'Edit poll', 'democracy-poll' ),
						'{logs_url}'  => esc_url( add_query_arg( [ 'ip'=>null, 'poll'=>$log->qid ] ) ),
						'{logs_text}' => __( 'Poll logs', 'democracy-poll' ),
					]
				);
			}

			return Kses::kses_html( $poll->question ) . $actions;
		}

		if( 'userid' === $column ){
			if( ! $user = $this->cache( 'users', $log->userid ) ){
				$user = $this->cache( 'users', $log->userid, $wpdb->get_row( "SELECT * FROM $wpdb->users WHERE ID = " . (int) $log->userid ) );
			}

			return esc_html( @ $user->user_nicename );
		}

		if( 'expire' === $column ){
			return date( 'Y-m-d H:i:s', $log->expire + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		}

		if( 'aids' === $column ){
			$out = [];
			foreach( explode( ',', $log->aids ) as $aid ){
				if( ! $answ = $this->cache( 'answs', $aid ) ){
					$answ = $this->cache( 'answs', $aid, $wpdb->get_row( "SELECT * FROM $wpdb->democracy_a WHERE aid = " . (int) $aid ) );
				}

				if( $answ ){
					$new = Admin_Page_Logs::is_new_answer( $answ )
						? sprintf( ' <a href="%s"><span style="color:red;">NEW</span></a>', Poll_Utils::edit_poll_url( $log->qid ) )
						: '';
					$out[] = '• ' . esc_html( $answ->answer ) . $new;
				}
				else {
					$out[] = "<span style=\"color:tomato\">Answer not found. ID: $aid</span>";
				}

			}

			return implode( '<br>', $out );
		}

		return $log->$column ?? print_r( $log, true );
	}

}
