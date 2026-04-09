<?php

namespace DemocracyPoll\Admin;

use DemocracyPoll\Helpers\Kses;
use DemocracyPoll\Poll_Utils;
use DemPoll;
use function DemocracyPoll\plugin;
use function DemocracyPoll\options;

class List_Table_Logs extends \WP_List_Table {

	private static array $cache;

	public int $poll_id;

	private Admin_Page_Logs $logs_page;

	public function __construct( Admin_Page_Logs $logs_page ) {
		$this->logs_page = $logs_page;

		parent::__construct( [
			'singular' => 'demlog',
			'plural'   => 'demlogs',
			'ajax'     => false,
		] );

		$this->bulk_action_handler();

		add_screen_option( 'per_page', [
			'label'   => 'Показывать на странице',
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
			plugin()->msg->add_error( __( 'Nothing was selected.', 'democracy-poll' ) );

			return;
		}

		// delete
		if( 'delete_only_logs' === $action ){
			$this->logs_page->del_only_logs( $log_ids );
		}

		// delete with votes
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
		if( $this->poll_id ){
			if( ! $poll = $this->cache( 'polls', $this->poll_id ) ){
				$poll = new DemPoll( $this->poll_id );
				$this->cache( 'polls', $this->poll_id, $poll );
			}

			echo sprintf( '<h2><small>%s</small>%s <small><a href="%s">%s</a></small></h2>',
				__( 'Poll\'s logs: ', 'democracy-poll' ),
				Kses::kses_html( $poll->question ),
				Poll_Utils::edit_poll_url( $this->poll_id ),
				__( 'Edit poll', 'democracy-poll' )
			);
		}
	}

	## Extra controls to be displayed between bulk actions and pagination
	public function extra_tablenav( $which ) {

		if( $which === 'top' ){
			$newfilter = ( $_GET['filter'] ?? '' ) === 'new_answers';

			echo '
			<div class="alignleft actions" style="margin-top:.3em;">
				' . ( options()->democracy_off ? '' :
					'<a class="button button-small" href="' . esc_url( add_query_arg( [ 'filter' => $newfilter ? null : 'new_answers' ] ) ) . '">' .
					( $newfilter ? ' &#215; ' : '' ) . __( 'NEW answers logs', 'democracy-poll' )
					. '</a>'
				) . '
			</div>
			';
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
	 * Fill columns.
	 *
	 * @param \stdClass $log     The log object form DB {@see $wpdb->democracy_log} table.
	 * @param string    $column  The column name.
	 */
	function column_default( $log, $column ) {
		global $wpdb;

		if( 'ip' === $column ){
			return sprintf( '<a title="%s" href="%s">%s</a>',
				__( 'Search by IP', 'democracy-poll' ),
				esc_url( add_query_arg( [ 'ip' => $log->ip, 'poll' => null ] ) ),
				esc_html( $log->ip )
			);
		}

		if( 'ip_info' === $column ){
			$country_img = '';
			$country_name = '';
			$city = '';

			// обновим данные IP если их нет и прошло больше суток с последней попытки
			if( $log->ip ){
				if( ! $log->ip_info || ( is_numeric( $log->ip_info ) && ( time() - DAY_IN_SECONDS ) > $log->ip_info ) ){
					$log->ip_info = \DemocracyPoll\Helpers\IP::prepared_ip_info( $log->ip );

					$wpdb->update( $wpdb->democracy_log, [ 'ip_info' => $log->ip_info ], [ 'logid' => $log->logid ] );
				}

				if( $log->ip_info && ! is_numeric( $log->ip_info ) ){
					[ $country_name, $county_code, $city ] = explode( ',', $log->ip_info );

					// css background position
					if( ! $flagcss = $this->cache( 'flagcss', 'flagcss' ) ){
						$flagcss = $this->cache( 'flagcss', 'flagcss', file_get_contents( plugin()->dir . '/admin/country_flags/flags.css' ) );
					}
					preg_match( "~flag-" . strtolower( $county_code ) . " \{([^}]+)\}~", $flagcss, $mm );
					$bg_pos = $mm[1] ?? '';

					$country_img = $bg_pos ? '<span title="' . $country_name . ( $city ? ", $city" : '' ) . '" style="cursor:help; display:inline-block; width:16px; height:11px; background:url(' . plugin()->url . '/admin/country_flags/flags.png) no-repeat; ' . $bg_pos . '"></span> ' : '';
				}
			}

			return $country_img
				? $country_img . ' <span style="opacity:0.7">' . $country_name . ( $city ? ", $city" : '' ) . '</span>'
				: '';
		}

		if( 'qid' === $column ){
			if( ! $poll = $this->cache( 'polls', $log->qid ) ){
				$poll = $this->cache( 'polls', $log->qid, \DemPoll::get_db_data( $log->qid ) );
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

				$new = Admin_Page_Logs::is_new_answer( $answ )
					? sprintf( ' <a href="%s"><span style="color:red;">NEW</span></a>', Poll_Utils::edit_poll_url( $log->qid ) )
					: '';

				$out[] = '- ' . esc_html( $answ->answer ) . $new;
			}

			return implode( '<br>', $out );
		}


		return $log->$column ?? print_r( $log, true );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param \stdClass $item The log item.
	 */
	public function column_cb( $item ): void {
		$logid = (int) $item->logid;
		echo '<label><input id="cb-select-' . $logid . '" type="checkbox" name="logids[]" value="' . $logid . '" /></label>';
	}

}
