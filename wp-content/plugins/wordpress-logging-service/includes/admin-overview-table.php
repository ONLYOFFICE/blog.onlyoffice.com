<?php

if ( !class_exists( 'WP_List_Table' ) ) require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/** @see http://codex.wordpress.org/Class_Reference/WP_List_Table
 *  @see http://wordpress.org/extend/plugins/custom-list-table-example/
 */
class WlsAdminOverviewTable extends WP_List_Table {
	
	
	private $_logs_by_id;
	private $_seen_entries;
	private $_blog_info_cache;
	
	
	function __construct( ) {
				
		$this->_logs_by_id = array();
		$this->_seen_entries = array();
		$this->_blog_info_cache = array();
		parent::__construct( array(
	    	'singular'  => 'entry',	//singular name of the listed records
	        'plural'    => 'entries',   //plural name of the listed records
	        'ajax'      => false        //does this table support ajax?
	    ) );
	}
	
	
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'id' => __( 'ID', WLS_TXD )."<br /><small>".__( "Was read", WLS_TXD )."</small>",
			'category' => __( 'Log category', WLS_TXD ),
			'time' => __( 'Time', WLS_TXD ),
			'blog' => __( 'Blog', WLS_TXD )."<br />".__( 'User', WLS_TXD ),
			'severity' => __( 'Severity', WLS_TXD ),
			'message' => __( 'Message', WLS_TXD ),
		);
		return $columns;
	}
	
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'time' => array( 'date', true )
		);
	    return $sortable_columns;
	}
	
	
	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', WLS_TXD ),
			'mark-seen' => __( 'Mark as read', WLS_TXD )
		);
		return $actions;
	}

	
	function prepare_items( $log_id = 'all', $seen = true, $min_severity = 0, $entry_id = 0 ) {
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$this->process_bulk_actions();
		
		extract( wls_get_settings() );
		
		$per_page = $log_entries_per_page;
		$current_page = $this->get_pagenum();
		
		$order = isset( $_REQUEST["order"] ) ? $_REQUEST["order"] : "ASC";
		
		/* Get entries for a page and their total count. */
		$this->items = wls_get_entries( $log_id, ( $current_page - 1 ) * $per_page, $current_page * $per_page, $seen, $order, $min_severity, 'entries', $entry_id );
		
		$total_items = wls_get_entries( $log_id, NULL, NULL, $seen, "NONE", $min_severity, "count" );
		
		/* Prepare log category information by IDs */
		$logs = wls_get_logs();
		foreach( $logs as $log ) {
			$this->_logs_by_id[$log->id] = $log;
		}
		
		$this->set_pagination_args( array(
	        'total_items' => $total_items,                  //WE have to calculate the total number of items
	        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
	        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
    	) );
	}
	
	
	function process_bulk_actions() {
		
		switch( $this->current_action() ) {
			case "delete":
				wls_delete_entries( $_REQUEST["entry"] );
				break;
			case "mark-seen":
				wls_mark_seen( $_REQUEST["entry"] );
				break;
		}
	}
	
	
	function single_row( $item ) {
		/* Prepare style */
		static $alt = '';
		$alt = ( $alt == '' ? "alternate" : '' );
		$row_class = "class=\" $alt severity-{$item->category} \"";
		
		/* Show the row */
		echo "<tr $row_class>";
		echo $this->single_row_columns( $item );
		echo '</tr>';
		
		/* Add the entry to list of seen. */
		$this->_seen_entries[] = $item->id;
	}
	
	
	function get_blog_info( $blog_id ) {
		if( !isset( $this->_blog_info_cache[$blog_id] ) ) {
			if( wls_is_network_mode() ) {
				switch_to_blog( $blog_id );
				$blog_name = get_bloginfo( 'name' );
				$blog_url = home_url();
				restore_current_blog();
			} else {
				$blog_name = get_bloginfo( 'name' );
				$blog_url = home_url();
			}
			$this->_blog_info_cache[$blog_id] = array(
				"url" => $blog_url,
				"name" => $blog_name
			);
		}
		return $this->_blog_info_cache[$blog_id];
	}
	
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="entry[]" value="%s" />', $item->id
		);
	}
	
	
	function column_id( $item ) {
		return "<code><small>{$item->id}</small></code><br/>
			<code><small>" . ( $item->seen ? "true" : "false" ) . "</small></code>";
	}
	
	
	function column_category( $item ) {
		return $this->_logs_by_id[$item->log_id]->log_name;
	}
	
	
	function column_time( $item ) {
		return $item->date;
	}
	
	
	function column_blog( $item ) {
		$blog_info = $this->get_blog_info( $item->blog_id );
		$blog = "<a href=\"{$blog_info["url"]}\" >{$blog_info["name"]}</a>";
		if( $item->user_id == 0 ) {
			$user = '-';
		} else {
			$userdata = get_userdata( $item->user_id );
			$user = $userdata->user_login;
		}
		return $blog."<br />".$user;
	}
		
	
	function column_severity( $item ) {
		return wls_severity_to_string( $item->category );
	}
	
	
	function column_message( $item ) {
		$text = esc_attr( $item->text );
		if( strlen( $text ) > 300 ) {
			$text = "<small>$text</small>";
		}
		return $text;
	}
	
	
	function print_mark_form() {
		?>
		<form method="GET">
			<input type="hidden" name="wls_action" value="mark-seen" />
			<input type="hidden" name="entries" value="<?php echo implode( ',', $this->_seen_entries ); ?>" />
			<input type="hidden" name="page" value="<?php echo $_REQUEST["page"]; ?>" />
			<?php
				/* Keep severity filter on it's current value. */
				if( isset( $_POST['switch_severity'] ) ) {
					echo "<input type=\"hidden\" name=\"switch_severity\" value=\"{$_POST['switch_severity']}\" />";
				}
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Mark all as read', WLS_TEXTDOMAIN ); ?>" />
			</p>
		</form>
		<?php
	}

}

?>
