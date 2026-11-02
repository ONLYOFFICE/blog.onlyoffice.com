<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace DemocracyPoll\Admin;

use DemocracyPoll\Support\Helpers;
use DemocracyPoll\Support\Kses;
use DemocracyPoll\Options;
use DemocracyPoll\Poll_Answer;
use DemocracyPoll\Poll_Utils;
use DemocracyPoll\Poll;
use DemocracyPoll\Plugin;
use WP_List_Table;

class List_Table_Polls extends WP_List_Table {

	private Plugin $plugin;
	private Options $options;

	public function __construct( Plugin $plugin, Options $options ) {
		$this->plugin = $plugin;
		$this->options = $options;

		parent::__construct( [
			'singular' => 'dempoll',
			'plural'   => 'dempolls',
			'ajax'     => false,
		] );
	}

	public function load(): void {
		// Per-page screen option.
		add_screen_option( 'per_page', [
			'label'   => __( 'Show on page', 'democracy-poll' ),
			'default' => 10,
			'option'  => 'dem_polls_per_page',
		] );

		$this->prepare_items();
	}

	public function prepare_items() {
		global $wpdb;

		$per_page = get_user_meta( get_current_user_id(), get_current_screen()->get_option( 'per_page', 'option' ), true ) ?: 10;

		$where = 'WHERE 1';
		if( $s = wp_unslash( $_GET['s'] ?? '' ) ){
			$like = '%' . $wpdb->esc_like( $s ) . '%';
			$where .= $wpdb->prepare( " AND ( question LIKE %s OR id IN (SELECT qid from $wpdb->democracy_a WHERE answer LIKE %s) ) ", $like, $like );
		}

		// pagination
		$this->set_pagination_args( [
			'total_items' => $wpdb->get_var( "SELECT count(*) FROM $wpdb->democracy_q $where" ),
			'per_page'    => $per_page,
		] );
		$cur_page = $this->get_pagenum(); // Must run after set_pagination_args().

		// order by
		$offset = ( $cur_page - 1 ) * $per_page;
		$OFFSET = "LIMIT $offset,$per_page";
		$order = ( isset( $_GET['order'] ) && $_GET['order'] === 'asc' ) ? 'ASC' : 'DESC';
		$orderby = sanitize_key( empty( $_GET['orderby'] ) ? 'id' : $_GET['orderby'] );
		$ORDER_BY = sprintf( "ORDER BY %s %s", $orderby, $order );

		$sql = "SELECT * FROM $wpdb->democracy_q $where $ORDER_BY $OFFSET";

		$this->items = array_map( 'democracy_get_poll', $wpdb->get_results( $sql ) );
	}

	public function get_columns(): array {
		$columns = [
			//'cb'        => '<input type="checkbox" />',
			'id'         => __( 'ID', 'democracy-poll' ),
			'question'   => __( 'Question', 'democracy-poll' ),
			'open'       => '<span class="dashicons dashicons-yes" title="' . __( 'Poll Opened', 'democracy-poll' ) . '"></span>',
			'active'     => '<span class="dashicons dashicons-controls-play" title="' . __( 'Active polls', 'democracy-poll' ) . '"></span>',
			'usersvotes' => '<span class="dashicons dashicons-admin-users" title="' . __( 'Users vote', 'democracy-poll' ) . '"></span>',
			'answers'    => __( 'Answers', 'democracy-poll' ),
			'in_posts'   => __( 'In posts', 'democracy-poll' ),
			'added'      => __( 'Added', 'democracy-poll' ),
		];

		return $columns;
	}

	public function get_hidden_columns(): array {
		return [];
	}

	public function get_sortable_columns(): array {
		return [
			'id'         => [ 'id', 'asc' ],
			'question'   => [ 'question', 'asc' ],
			'open'       => [ 'open', 'asc' ],
			'active'     => [ 'active', 'asc' ],
			'usersvotes' => [ 'users_voted', 'asc' ],
			'added'      => [ 'added', 'asc' ],
		];
	}

	/**
	 * @param Poll   $poll
	 * @param string $column
	 */
	public function column_default( $poll, $column ) {
		global $wpdb;
		static $cache;

		if( ! isset( $cache[ $poll->id ] ) ){
			$cache[ $poll->id ] = $wpdb->get_results( "SELECT * FROM $wpdb->democracy_a WHERE qid = " . (int) $poll->id );
		}

		if( method_exists( $this, "col__$column" ) ){
			/**
			 * @see self::col__question()
			 * @see self::col__usersvotes()
			 * @see self::col__in_posts()
			 * @see self::col__answers()
			 * @see self::col__active()
			 * @see self::col__open()
			 * @see self::col__added()
			 */
			return $this->{"col__$column"}( $poll );
		}

		return $poll->$column ?? print_r( $poll, true );
	}

	private function col__question( Poll $poll ): string {
		global $wpdb;
		$admurl = $this->plugin->admin_page_url;

		$statuses =
			'<span class="statuses">' .
			( $poll->democratic ? '<span class="dashicons dashicons-megaphone" title="' . __( 'Users can add answers (democracy).', 'democracy-poll' ) . '"></span>' : '' ) .
			( $poll->revote ? '<span class="dashicons dashicons-update" title="' . __( 'Users can revote', 'democracy-poll' ) . '"></span>' : '' ) .
			( $poll->forusers ? '<span class="dashicons dashicons-admin-users" title="' . __( 'Only for registered user.', 'democracy-poll' ) . '"></span>' : '' ) .
			( $poll->multiple ? '<span class="dashicons dashicons-image-filter" title="' . __( 'Users can choose many answers (multiple).', 'democracy-poll' ) . '"></span>' : '' ) .
			( $poll->show_results ? '<span class="dashicons dashicons-visibility" title="' . __( 'Allow to watch the results of the poll.', 'democracy-poll' ) . '"></span>' : '' ) .
			'</span>';

		// actions
		$actions = [];
		// user can edit
		if( Poll_Utils::cuser_can_edit_poll( $poll ) ){
			// edit
			$actions[] = sprintf(
				'<span class="edit"><a href="%s">%s</a> | </span>',
				Poll_Utils::edit_poll_url( $poll->id ),
				__( 'Edit', 'democracy-poll' )
			);

			// logs
			$has_logs = $wpdb->get_var( $wpdb->prepare( "SELECT qid FROM $wpdb->democracy_log WHERE qid=%d LIMIT 1", $poll->id ) );
			if( $has_logs ){
				$actions[] = sprintf(
					'<span class="edit"><a href="%s">%s</a> | </span>',
					add_query_arg( [ 'subpage' => 'logs', 'poll' => $poll->id ], $admurl ),
					__( 'Logs', 'democracy-poll' )
				);
			}

			// delete
			$actions[] = '<span class="delete"><a href="' . Admin_Page::add_nonce( add_query_arg( [ 'delete_poll' => $poll->id ], $admurl ) ) . '" onclick="return confirm(\'' . __( 'Are you sure?', 'democracy-poll' ) . '\');">' . __( 'Delete', 'democracy-poll' ) . '</a> | </span>';
		}

		// shortcode
		$actions[] = '<span style="color:#999">' . Admin_Page_Edit_Poll::shortcode_html( $poll->id ) . '</span>';

		return $statuses . Kses::kses_html( $poll->question ) . '<div class="row-actions">' . implode( " ", $actions ) . '</div>';
	}

	private function col__usersvotes( Poll $poll ): string {
		$votes_sum = array_sum( wp_list_pluck( $poll->answers, 'votes' ) );

		return $poll->multiple ? '<span title="' . __( 'voters / votes', 'democracy-poll' ) . '">' . $poll->users_voted . ' <small>/ ' . $votes_sum . '</small></span>' : $votes_sum;
	}

	private function col__in_posts( Poll $poll ): string {
		if( ! $posts = Helpers::get_posts_with_poll( $poll ) ){
			return '';
		}

		$out = [];

		$__substr = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
		foreach( $posts as $post ){
			$out[] = '<a href="' . get_permalink( $post ) . '">' . $__substr( $post->post_title, 0, 80 ) . ' ...</a>';
		}

		$_style = ' style="margin-bottom:0; line-height:1.4;"';

		return ( count( $out ) > 1 )
			? '<ol class="in__posts" style="margin:0 0 0 1em;"><li' . $_style . '>' . implode( '</li><li' . $_style . '>', $out ) . '</li></ol>'
			: $out[0];
	}

	private function col__answers( Poll $poll ): string {
		if( ! $poll->answers ){
			return 'No';
		}

		$answers = $poll->answers;
		usort( $answers, static fn( $a, $b ) => $b->votes <=> $a->votes );

		$_answ = [];
		foreach( $answers as $answer ){
			$answ_row = sprintf( '<small>%s</small> %s', $answer->votes, Kses::kses_html( $answer->answer ) );
			/**
			 * Allows to modify the answer row before it is output in the list table.
			 *
			 * @param string      $answ_row The row of the answer.
			 * @param Poll_Answer $answer   The answer object.
			 */
			$_answ[] = apply_filters( 'dem_admin_polls_list_answers_column_row', $answ_row, $answer );
		}

		return '<div class="compact-answ">' . implode( '<br>', $_answ ) . '</div>';
	}

	private function col__active( Poll $poll ): string {
		return Poll_Utils::cuser_can_edit_poll( $poll ) ? Admin_Page_Edit_Poll::activate_button( $poll, true, 'small' ) : '';
	}

	private function col__open( Poll $poll ): string {
		return Poll_Utils::cuser_can_edit_poll( $poll ) ? Admin_Page_Edit_Poll::open_button( $poll, true, 'small' ) : '';
	}

	private function col__added( Poll $poll ): string {
		$date_format = get_option( 'date_format' );

		$date = date( $date_format, $poll->added );
		$end = $poll->end ? date( $date_format, $poll->end ) : '';

		return "$date<br>$end";
	}

	/** @param Poll $poll */
	public function column_cb( $poll ) {
		echo '<label><input id="cb-select-' . $poll->id . '" type="checkbox" name="delete[]" value="' . $poll->id . '" /></label>';
	}

	public function search_box( $text, $wrap_attr = '' ) {
		if( empty( $_REQUEST['s'] ) && ! $this->has_items() ){
			return;
		}

		$query = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY );
		parse_str( $query, $arr );

		$inputs = '';
		foreach( $arr as $k => $v ){
			$inputs .= '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
		}
		?>
		<form action="" method="get" class="search-form">
			<?= $inputs ?>
			<p class="polls-search-box" <?= $wrap_attr ?>>
				<label class="screen-reader-text"><?= $text ?>:</label>
				<input type="search" name="s" value="<?php _admin_search_query() ?>"/>
				<?php submit_button( $text, 'button', '', false, [ 'id' => 'search-submit' ] ) ?>
			</p>
		</form>
		<?php
	}

}
