<?php
ob_start();

class Pma_Results_List_Table extends WP_List_Table {
	private $plugin_name;
	private $title_length;

	/** Class constructor */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
		$this->title_length = Poll_Maker_Ays_Admin::get_listtables_title_length('polls');
		parent::__construct(array(
			'singular' => __('Result', $this->plugin_name), //singular name of the listed records
			'plural'   => __('Results', $this->plugin_name), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
		));
		add_action('admin_notices', array($this, 'results_notices'));

	}

	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_reports( $per_page = 50, $page_number = 1 ) {

		global $wpdb;
		$reports_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$answ_table    = esc_sql($wpdb->prefix."ayspoll_answers");
		$polls_table   = esc_sql($wpdb->prefix."ayspoll_polls");

		$sql = "SELECT
        ".$reports_table.".id AS id,
        ".$reports_table.".answer_id, 
        ".$reports_table.".user_ip, 
        ".$reports_table.".user_id, 
        ".$reports_table.".vote_date, 
        ".$reports_table.".unread, 
        ".$reports_table.".other_info, 
        ".$reports_table.".multi_answer_ids, 
        ".$answ_table.".id AS ans_id, 
        ".$answ_table.".answer, 
        ".$answ_table.".votes,
		".$polls_table.".categories
        FROM
        ".$reports_table."
        JOIN
        ".$answ_table."
        ON ".$answ_table.".id = ".$reports_table.".answer_id 
		JOIN ".$polls_table."
		ON ".$answ_table.".poll_id = ".$polls_table.".id";

        $args = array();
		$where_cond = array();

		if (isset($_REQUEST['orderbypoll']) && $_REQUEST['orderbypoll'] > 0) {
			$poll_id = absint(sanitize_text_field( $_REQUEST['orderbypoll'] ));
			$args[]  = $poll_id;

			$where_cond[] = " ".$reports_table.".answer_id IN (SELECT ".$answ_table.".id FROM ".$answ_table." WHERE ".$answ_table.".poll_id=%s) ";
		}

		if (isset($_REQUEST['orderbycat']) && $_REQUEST['orderbycat'] > 0) {
			$cat_id 		= absint(sanitize_text_field( $_REQUEST['orderbycat'] ));
			$where_cond[] 	= sprintf(" categories LIKE('%%,%s,%%') ", esc_sql( $wpdb->esc_like( $cat_id ) ) );
		}

		$search = ( isset( $_REQUEST['s'] ) ) ? esc_sql( sanitize_text_field( $_REQUEST['s'] ) ) : false;
		if ( $search ) {
			if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
				$where_cond[] = sprintf( " ".$reports_table.".other_info LIKE('%%%s%%') ", esc_sql( $wpdb->esc_like( $search ) ) );
			}
			else{
				$where_cond[] = sprintf( " ".$answ_table.".answer LIKE('%%%s%%') ", esc_sql( $wpdb->esc_like( $search ) ) );
			}
		}
		if( !empty($where_cond) ){
			$where_cond = " WHERE " . implode( " AND ", (($where_cond)) );
		}
		else{
			$where_cond = "";
		}
		$sql .= $where_cond;

		if ( ! empty( $_REQUEST['orderby'] ) ) {

            $order_by  = ( isset( $_REQUEST['orderby'] ) && sanitize_text_field( $_REQUEST['orderby'] ) != '' ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id';
            $order_by .= ( ! empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) == 'asc' ) ? ' ASC' : ' DESC';

            $sql_orderby = sanitize_sql_orderby($order_by);

            if ( $sql_orderby ) {
                $sql .= ' ORDER BY ' . $sql_orderby;
            } else {
                $sql .= ' ORDER BY id DESC';
            }
        }else{
            $sql .= ' ORDER BY id DESC';
        }

		$sql .= " LIMIT %d";
		$args[] = $per_page;
		$offset = ($page_number - 1) * $per_page;
		$sql .= " OFFSET %d";
		$args[] = $offset;

		$result = $wpdb->get_results(
		  	$wpdb->prepare( $sql, $args),
		  	'ARRAY_A'
		);

		return $result;
	}

	public function get_report_by_id( $id ) {
		global $wpdb;
		$report_id = absint(sanitize_text_field($id));
		$report_table = esc_sql($wpdb->prefix."ayspoll_reports");
		$sql  = "SELECT * FROM ".$report_table." WHERE id=%d";
		$result = $wpdb->get_row(
		  	$wpdb->prepare( $sql, $report_id),
		  	'ARRAY_A'
		);

		return $result;
	}

	public static function get_answer_by_id( $id ) {
		global $wpdb;

		$answ_id 	= absint(sanitize_text_field($id));
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");

		$sql = "SELECT * FROM ".$answ_table." WHERE id=%d";

		$result = $wpdb->get_row(
		  	$wpdb->prepare( $sql, $answ_id),
		  	'ARRAY_A'
		);

		return $result;
	}

	public function get_polls() {
		global $wpdb;

		$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");
		$sql = "SELECT * FROM ".$poll_table;

		$result = $wpdb->get_results($sql, 'ARRAY_A');

		return $result;
	}

	public function mark_as_read() {
		global $wpdb;
		$results_table = $wpdb->prefix . "ayspoll_reports";
		$res           = $wpdb->update(
			$results_table,
			array('unread' => 0),
			array('unread' => 1),
			array('%d'),
			array('%d')
		);
		if ($res) {
			return true;
		}
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_reports( $id, $answer_id ) {

		global $wpdb;
		$answer = self::get_answer_by_id($answer_id);
		$votes = intval($answer['votes']);
		$votes--;
		if ($votes < 0) {
			$votes = 0;
		}
		
		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
		$rep_table 	= esc_sql($wpdb->prefix."ayspoll_reports");

		$arg_id 	 = esc_sql($id);
		$arg_answ_id = esc_sql($answer_id);
		$arg_votes 	 = esc_sql($votes);

		$wpdb->update( 
			$answ_table,
	        array(
	            'votes' => $arg_votes
	        ),
	        array( 'id' => $arg_answ_id ),
		    array( '%d' ),
		    array( '%d' )
		 );

		$wpdb->delete( $rep_table,
			array('id' => $arg_id),
			array('%d')
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$reports_table 	= esc_sql($wpdb->prefix."ayspoll_reports");
		$answ_table 	= esc_sql($wpdb->prefix."ayspoll_answers");

        $filter = array();
		$sql = "SELECT COUNT(*) FROM ".$reports_table;

        if( isset( $_REQUEST['orderbypoll'] ) && intval($_REQUEST['orderbypoll']) > 0){
            $poll_id = absint( sanitize_text_field( $_REQUEST['orderbypoll'] ) );
            $filter[] = " ".$reports_table.".answer_id IN (SELECT ".$answ_table.".id FROM ".$answ_table." WHERE ".$answ_table.".poll_id=". $poll_id  .")";
        }

        if(count($filter) !== 0){
            $sql .= " WHERE ".implode(" AND ", $filter);
        }

        return $wpdb->get_var( $sql );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e('There are no results yet.', $this->plugin_name);
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$other_info = !empty($item['other_info']) ? json_decode($item['other_info']) : array();
		switch ( $column_name ) {
			case 'poll_id':
			case 'poll_title':
			case 'user_ip':
			case 'answer':
			case 'vote_date':
			case 'vote_reason':
			case 'unread':
				return $item[$column_name];
				break;
			case 'user_id':
				// return $item[$column_name] > 0 ? get_user_by('ID', $item[$column_name])->display_name : __("Guest", $this->plugin_name);
				$current_user_name = __("Guest", $this->plugin_name);
				if($item[$column_name] > 0){
					$current_user_name = isset(get_userdata($item[$column_name])->display_name) ? get_userdata($item[$column_name])->display_name : __("Guest", $this->plugin_name);
				}
				return $current_user_name;
				break;
			case 'user_name':
				if (!empty($other_info)) {
					return isset($other_info->name) ? $other_info->name : "";
				} else {
					return "";
				}
				break;
			case 'user_email':
				if (!empty($other_info)) {
					return isset($other_info->email) ? $other_info->email : "";
				} else {
					return "";
				}
				break;
			case 'user_phone':
				if (!empty($other_info)) {
					return isset($other_info->phone) ? $other_info->phone : "";
				} else {
					return "";
				}
				break;
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s">', $item['id']
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_poll_id( $item ) {
		global $wpdb;

		$answ_id 	  = absint(sanitize_text_field($item['answer_id']));

		$answ_table   = esc_sql($wpdb->prefix."ayspoll_answers");
		$poll_table   = esc_sql($wpdb->prefix."ayspoll_polls");

		$sql = "SELECT * FROM ".$answ_table." WHERE id=%d";

		$result = $wpdb->get_row(
		  	$wpdb->prepare( $sql, $answ_id),
		  	'ARRAY_A'
		);
		$res_id = absint(sanitize_text_field($result['poll_id']));
		$res_sql = "SELECT * FROM ".$poll_table." WHERE id=%d";

		$res = $wpdb->get_row(
		  	$wpdb->prepare( $res_sql, $res_id),
		  	'ARRAY_A'
		);

		$title = absint($res['id']);

		return $title;
	}

	function column_poll_title( $item ) {
		global $wpdb;
		$delete_nonce = wp_create_nonce($this->plugin_name . '-delete-result');
		$id 		  = absint(sanitize_text_field($item['answer_id']));

		$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
		$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");

		$sql = "SELECT * FROM ".$answ_table." WHERE id=%d";

		$result = $wpdb->get_row(
		  	$wpdb->prepare( $sql, $id),
		  	'ARRAY_A'
		);
		$poll_id = absint(sanitize_text_field($result['poll_id']));
		$res_sql = "SELECT * FROM ".$poll_table." WHERE id=%d";		
		$res = $wpdb->get_row(
		  	$wpdb->prepare( $res_sql, $poll_id),
		  	'ARRAY_A'
		);

		$title = stripslashes(sanitize_text_field($res['title']));

		$restitle = Poll_Maker_Ays_Admin::ays_restriction_string("word", $title, $this->title_length);

		$actions = [
			'delete' => sprintf('<a href="?page=%s&action=%s&result=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce),
		];
		$ready_title = "<span title='".$title."'>".$restitle."</span>";
		return $ready_title . $this->row_actions($actions);
	}

	function column_answer( $item ) {
		global $wpdb;
		$result_table = esc_sql($wpdb->prefix."ayspoll_reports");

		$multi_ids = array();
		$multi_ids = isset($item['multi_answer_ids']) && $item['multi_answer_ids'] != ""  ? json_decode($item['multi_answer_ids']) : $multi_ids;
		$multi_ids = implode("," , $multi_ids);

		if(isset($multi_ids) && !empty($multi_ids)){

			$id = absint(intval($item['answer_id']));
			$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
			$sql = "SELECT * FROM ".$answ_table." WHERE id IN (".$multi_ids.")";
			$result = $wpdb->get_results($sql , "ARRAY_A");
			if(isset($result[0])){
				$poll_type = $this->get_poll_type($result[0]['poll_id']);
				if(isset($poll_type['type']) && $poll_type['type'] == "voting"){
						$result[0]['answer'] = $poll_type['type'] == 'voting' && $result[0]['answer'] == '1' ? 'Like' :  $result[0]['answer'];
						$result[0]['answer'] = $poll_type['type'] == 'voting' && $result[0]['answer'] == '-1' ? 'Dislike' :  $result[0]['answer'];
						return stripslashes($result[0]['answer']);
				}
				else{
					$answers = array();
					foreach($result as $key => $value){
						$answers[] = isset($value['answer']) ? $value['answer'] : "";
					}

					foreach($answers as $answer => $answer_value){
						$answers[$answer] = Poll_Maker_Ays_Admin::ays_restriction_string("word", $answer_value, 5);
						$answer_for_title[$answer] = $answer_value;
					}

					$ready_title = "<span title='".strip_tags(stripslashes(implode("," , $answer_for_title)))."'>".strip_tags(stripslashes(implode("," , $answers)))."</span>";
					return $ready_title;
				}
			}
		}
		else{
			$id = absint(intval($item['answer_id']));
			$answ_table = esc_sql($wpdb->prefix."ayspoll_answers");
			$sql = "SELECT * FROM ".$answ_table." WHERE id=%d";
	
			$result = $wpdb->get_row(
				$wpdb->prepare( $sql, $id),
				'ARRAY_A'
			);
			$poll_type = $this->get_poll_type($result['poll_id']);
	
			$result['answer'] = $poll_type['type'] == 'voting' && $result['answer'] == '1' ? 'Like' :  $result['answer'];
			$result['answer'] = $poll_type['type'] == 'voting' && $result['answer'] == '-1' ? 'Dislike' :  $result['answer'];
 
			return stripslashes($result['answer']);
		}

	}

	function column_vote_date( $item ) {
		return date('H:i:s d.m.Y', strtotime($item['vote_date']));
	}

	function column_unread( $item ) {
		$unread = $item['unread'] == 1 ? "unread-result" : "";

		return "<div class='unread-result-badge $unread'></div>";
	}

	function column_vote_reason( $item ) {
		$info = isset($item['other_info']) ? json_decode($item['other_info'], true) : array();
		return isset($info['vote_reason']) ? esc_attr($info['vote_reason']) : '';
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'poll_title'  => __('Poll', $this->plugin_name),
			'user_ip'     => __('User IP', $this->plugin_name),
			'user_id'     => __('WP User', $this->plugin_name),
			'user_name'   => __('User Name', $this->plugin_name),
			'user_email'  => __('User Email', $this->plugin_name),
			'user_phone'  => __('User Phone', $this->plugin_name),
			'answer'      => __('Answer', $this->plugin_name),
			'vote_date'   => __('Vote Datetime', $this->plugin_name),
			'vote_reason' => __('Vote Reason', $this->plugin_name),
			'unread'      => __('Read Status', $this->plugin_name),
			'poll_id'     => __('Poll ID', $this->plugin_name),
		);

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'       		=> array('id', true),
			'user_ip'   	=> array('user_ip', true),
			'user_id'   	=> array('user_id', true),
			'answer' 		=> array('answer_id', true),
			'vote_date' 	=> array('vote_date', true),
			'unread'		=> array('unread', true),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete',
		);

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page('poll_results_per_page', 50);

		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page, //WE have to determine how many items to show on a page
		));

		$this->items = self::get_reports($per_page, $current_page);
	}

	public function process_bulk_action() {	
		//Detect when a bulk action is being triggered...
		$message = 'deleted';
		if ('delete' === $this->current_action()) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr($_REQUEST['_wpnonce']);

			if (!wp_verify_nonce($nonce, $this->plugin_name . '-delete-result')) {
				die('Go get a life script kiddies');
			} else {
				global $wpdb;
				$result = $this->get_report_by_id($_GET['result']);
				self::delete_reports(absint($_GET['result']), $result['answer_id']);

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url

				$url = esc_url_raw(remove_query_arg(array('action', 'result', '_wpnonce' , 'active-tab'))) . '&status=' . $message;
				wp_redirect($url);
			}

		}

		// If the delete bulk action is triggered
		if ((isset($_POST['action']) && 'bulk-delete' == $_POST['action'])
		    || (isset($_POST['action2']) && 'bulk-delete' == $_POST['action2'])
		) {

			$delete_ids = ( isset( $_POST['bulk-delete'] ) && ! empty( $_POST['bulk-delete'] ) ) ? esc_sql( $_POST['bulk-delete'] ) : array();

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				$res = $this->get_report_by_id($id); 		
				self::delete_reports($id, $res['answer_id'] );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url

			$url = esc_url_raw(remove_query_arg(['action', 'result', '_wpnonce', 'active-tab'])) . '&status=' . $message;
			wp_redirect($url);
		}
	}

	public function results_notices() {
		$status = (isset($_REQUEST['status'])) ? sanitize_text_field($_REQUEST['status']) : '';

		if (empty($status)) {
			return;
		}

		if ('deleted' == $status) {
			$updated_message = esc_html(__('Result deleted.', $this->plugin_name));
		}

		if (empty($updated_message)) {
			return;
		}

		?>
        <div class="notice notice-success is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
		<?php
	}

	public function get_poll_type($item) {
		global $wpdb;

		$id = absint(sanitize_text_field($item));

		$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");
		$sql = "SELECT type FROM ".$poll_table." WHERE id=%d";

		$result = $wpdb->get_row(
		  	$wpdb->prepare( $sql, $id),
		  	'ARRAY_A'
		);

		return $result;
	}

	public function get_categories() {
		global $wpdb;
		$category_table = $wpdb->prefix . 'ayspoll_categories';

        $sql = "SELECT id,title FROM ".$category_table." ORDER BY title";
        $results = $wpdb->get_results($sql , "ARRAY_A");

		if(isset($results) && !empty($results)){
			return $results;
		}
		else{
			return array();
		}
	}
	

}
