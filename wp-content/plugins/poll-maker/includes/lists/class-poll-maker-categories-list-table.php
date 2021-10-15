<?php
ob_start();

class Pma_Categories_List_Table extends WP_List_Table {
	private $plugin_name;

	/** Class constructor */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
		parent::__construct(array(
			'singular' => __('Category', $this->plugin_name), //singular name of the listed records
			'plural'   => __('Categories', $this->plugin_name), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
		));
		add_action('admin_notices', array($this, 'poll_category_notices'));

	}

	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_poll_categories( $per_page = 20, $page_number = 1 ) {

		global $wpdb;
		$cat_table = esc_sql($wpdb->prefix."ayspoll_categories");
		$sql = "SELECT * FROM ".$cat_table;
		$args = array();

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

	public function get_poll_category( $id ) {
		global $wpdb;

		$cat_id = absint(sanitize_text_field($id));
		$cat_table = esc_sql($wpdb->prefix."ayspoll_categories");
		$sql = "SELECT * FROM ".$cat_table." WHERE id=%d";
		$result = $wpdb->get_row(
			   	  		$wpdb->prepare( $sql, $cat_id),
				   	  	'ARRAY_A'
				  	  );

		return $result;
	}

	public function add_edit_poll_category( $data, $id = null, $ays_change_type = "" ) {
		global $wpdb;
		$cats_table = $wpdb->prefix . 'ayspoll_categories';

		if (isset($data["poll_category_action"]) && wp_verify_nonce($data["poll_category_action"], 'poll_category_action')) {
			$title       = isset($data['ays_title']) && $data['ays_title'] != "" ? stripslashes(sanitize_text_field($data['ays_title'])) :  "Category";
			if(function_exists("sanitize_textarea_field")){
				$description = isset($data['ays_description']) && $data['ays_description'] != "" ? stripslashes(sanitize_textarea_field($data['ays_description'])) : "";
			}
			else{
				$description = isset($data['ays_description']) && $data['ays_description'] != "" ? stripslashes(sanitize_text_field($data['ays_description'])) : "";
			}
			
			$skip_poll   = isset($data['ays_poll_allow_skip']) && $data['ays_poll_allow_skip'] == "allow" ? sanitize_text_field($data['ays_poll_allow_skip']) : "";
			$next_text   = isset($data['ays_poll_next_text']) && $data['ays_poll_next_text'] != "" ? sanitize_text_field($data['ays_poll_next_text']) : "Next";
			$default_message = 'The polls that belong to this category are expired or unpublished';
			$exp_message   = (isset($data['ays_poll_cat_message']) && $data['ays_poll_cat_message'] != '') ? sanitize_text_field($data['ays_poll_cat_message']) : $default_message;
			$previous_text = (isset($data['ays_poll_previous_text']) && $data['ays_poll_previous_text'] != '') ? sanitize_text_field($data['ays_poll_previous_text']) : 'Previous';
			$message     = '';
			$options     = array(
				"allow_skip"    => $skip_poll,
				"next_text"     => $next_text,
				"exp_message"   => $exp_message,
				"previous_text" => $previous_text
			);
			if ($id == null) {
				$result  = $wpdb->insert(
					$cats_table,
					array(
						'title'       => $title,
						'description' => $description,
						'options'     => json_encode($options, true),
					),
					array('%s', '%s', '%s')
				);
				$message = 'created';
				$last_id = $wpdb->insert_id;
			} else {
				$result  = $wpdb->update(
					$cats_table,
					array(
						'title'       => $title,
						'description' => $description,
						'options'     => json_encode($options, true),
					),
					array('id' => $id),
					array('%s', '%s', '%s'),
					array('%d')
				);
				$message = 'updated';
			}

			if ($result >= 0) {
				if ('' != $ays_change_type) {
					if($id == null){
						$url = esc_url_raw( add_query_arg( array(
							"action"    	 => "edit",
							"poll_category"  => $last_id,
							"status"    	 => $message
						) ) );
					}
					else{
						$url = esc_url_raw( remove_query_arg(false) ) . '&status=' . $message;
					}					
					wp_redirect($url);
				} else {
					$url = esc_url_raw(remove_query_arg(array('action', 'poll_category'))) . '&status=' . $message;
					wp_redirect($url);
				}
			}
		}
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_poll_categories( $id ) {
		global $wpdb;
		$cat_table = esc_sql($wpdb->prefix."ayspoll_categories");
		$wpdb->delete(
			$cat_table,
			array('id' => $id),
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
		$cat_table = esc_sql($wpdb->prefix."ayspoll_categories");
		$sql = "SELECT COUNT(*) FROM ".$cat_table;

		return $wpdb->get_var($sql);
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e('There are no poll categories yet.', $this->plugin_name);
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
		switch ( $column_name ) {
			case 'title':
			case 'description':
			case 'id':
				return $item[$column_name];
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
		if(intval($item['id']) === 1){
            return;
        }
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s">', $item['id']
		);
	}

	function column_shortcode( $item ) {
		return sprintf('<input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="[ays_poll cat_id=%s]" />', $item["id"]);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_title( $item ) {
		$delete_nonce = wp_create_nonce($this->plugin_name . '-delete-poll-category');

		$title = sprintf('<a href="?page=%s&action=%s&poll_category=%d"><strong>%s</strong></a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id']) ,esc_attr($item['title']));

		$actions = array(
			'edit'   => sprintf('<a href="?page=%s&action=%s&poll_category=%d">' . __('Edit', $this->plugin_name) . '</a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id'])),
			// 'delete' => sprintf('<a href="?page=%s&action=%s&poll_category=%s&_wpnonce=%s">' . __('Delete', $this->plugin_name) . '</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce),
		);

		if(intval($item['id']) !== 1){
            $actions['delete'] = sprintf('<a href="?page=%s&action=%s&poll_category=%s&_wpnonce=%s">' . __('Delete', $this->plugin_name) . '</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce);
        }

		return $title . $this->row_actions($actions);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox">',
			'title'       => __('Title', $this->plugin_name),
			'description' => __('Description', $this->plugin_name),
			'shortcode'   => __('Shortcode', $this->plugin_name),
			'id'          => __('ID', $this->plugin_name),
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
			'title' => array('title', true),
			'id'    => array('id', true),
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
			'bulk-delete' => __('Delete', $this->plugin_name),
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

		$per_page     = $this->get_items_per_page('poll_cats_per_page', 20);
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page, //WE have to determine how many items to show on a page
		));

		$this->items = self::get_poll_categories($per_page, $current_page);
	}

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ('delete' === $this->current_action()) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr($_REQUEST['_wpnonce']);

			if (!wp_verify_nonce($nonce, $this->plugin_name . '-delete-poll-category')) {
				die('Go get a life script kiddies');
			} else {
				self::delete_poll_categories(absint($_GET['poll_category']));

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url

				$url = esc_url_raw(remove_query_arg(['action', 'poll_category', '_wpnonce'])) . '&status=deleted';
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
				self::delete_poll_categories($id);

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url
			$url = esc_url_raw(remove_query_arg(['action', 'poll_category', '_wpnonce'])) . '&status=deleted';
			wp_redirect($url);
		}
	}

	public function poll_category_notices() {
		$status = (isset($_REQUEST['status'])) ? sanitize_text_field($_REQUEST['status']) : '';

		if (empty($status)) {
			return;
		}

		if ('created' == $status) {
			$updated_message = esc_html(__('Poll category created.', $this->plugin_name));
		} elseif ('updated' == $status) {
			$updated_message = esc_html(__('Poll category saved.', $this->plugin_name));
		} elseif ('deleted' == $status) {
			$updated_message = esc_html(__('Poll category deleted.', $this->plugin_name));
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
}