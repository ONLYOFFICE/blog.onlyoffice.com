<?php

/*****************************************************************************\
		(SUPER)ADMIN OVERVIEW
\*****************************************************************************/


function wls_get_pagenow( $page = '' ) {
	global $pagenow;
	$result = $pagenow;
	if( empty( $page ) ) {
		$result.= '?page='.$_GET['page'];
	} else {
		$result.= '?page='.$page;
	}
	return $result;
}



function wls_superadmin_overview_page() {

	wls_flush_last_log();
	
	if( isset($_REQUEST['wls_action']) ) {
        $action = $_REQUEST['wls_action'];
    } else {
        $action = 'default';
    }
    
    switch( $action ) {
    case 'view':
    	wls_superadmin_overview_page_view( $_REQUEST['id'] );
    	break;
    case 'clear':
    	$log = wls_get_log( $_GET['id'] );
    	wls_clear( $log->log_name );
    	wls_superadmin_overview_page_default();
    	break;
    case 'register':
    	wls_register( $_POST['logname'], $_POST['description'] );
    	wls_superadmin_overview_page_default();
    	break;
    case 'unregister':
    	$log = wls_get_log( $_GET['id'] );
    	wls_unregister( $log->log_name );
    	wls_superadmin_overview_page_default();
    	break;
    case 'mark-seen':
    	$entries = explode( ',', $_REQUEST['entries'] );
    	wls_mark_seen( $entries );
		wls_superadmin_overview_page_default();
		break;
    default:
    	wls_superadmin_overview_page_default();
    	break;
    }
   
}


function wls_superadmin_overview_page_default() {
	
	extract( wls_get_settings() );
	
	?>
	<div class="wrap">
		<h2><?php _e( 'WLS system logs', WLS_TEXTDOMAIN ); ?></h2>
		<p><?php _e( 'Select log category:', WLS_TEXTDOMAIN ); ?>
		
		<!-- Log category list -->
		<ul>
			<?php
				$logs = wls_get_logs();
				foreach( $logs as $log ) {
					?>
					<li>
						<?php
							echo "<strong><a href=\"".wls_get_pagenow()."&wls_action=view&id={$log->id}\">{$log->log_name}</a></strong>&nbsp;-&nbsp;".esc_attr( $log->description )."&nbsp;";
							echo "<a href=\"".wls_get_pagenow()."&wls_action=clear&id={$log->id}\" title=\"".__( "Remove all log entries from this category.", WLS_TXD )."\">&curren;</a>&nbsp;";
							echo "<a href=\"".wls_get_pagenow()."&wls_action=unregister&id={$log->id}\" title=\"".__( "Delete this log category permanently (with all entries).", WLS_TXD )."\" >&times;</a>";
						?>
					</li>
					<?php
				}
			?>
		</ul>
		
		<!-- Registration of new category -->
		<form method="post">
			<input type="hidden" name="wls_action" value="register" />
			<p>
				<label for="logname"><?php _e( 'Register new log category:', WLS_TEXTDOMAIN ); ?></label>&nbsp;<input type="text" name="logname" />&nbsp;<input type="text" cols="40" name="description" />&nbsp;<input type="submit" value="<?php _e( 'Register', WLS_TEXTDOMAIN ); ?>" />
			</p>
		</form>
		
		<!-- Show severity filter and return it's current setting. -->
		<?php $min_severity = wls_show_severity_filter(); ?>
		
		<!-- Log entry table -->
		<h3><?php _e( "New log entries from all categories", WLS_TXD ); ?></h3>
		<form method="get">
			<?php
				// TODO prepinani unseen/all
				// TODO filtrovani logu (switch on/off)
				
				$table = new WlsAdminOverviewTable();
				
				/* Show a single entry or a list? */
				if( isset( $_REQUEST['entry_id'] ) ) {
					$table->prepare_items( "all", true, 0, $_REQUEST['entry_id'] );
				} else {
					$table->prepare_items( "all", false, $min_severity );
				}
				
				$table->display();
				
				if( get_current_user_id() == $wls_manager_id ) {
					$table->print_mark_form();
				}
			?>
		</form>
	</div>
	<?php
}



function wls_show_severity_filter( $args = array( 'wls_action' => 'default' ), $show_unread_info = true ) {

	//print_r( $args );

	extract( wls_get_settings() );

	if( isset( $_POST['switch_severity'] ) ) {
		$sev_str = explode( ' ', $_POST['switch_severity'] );
		$min_severity = wls_string_to_severity( $sev_str[0] );
	} else {
		$min_severity = $def_severity_filter;
	}
	
	if( $show_unread_info ) {
		// unseen counts
		$unseen = array();
		for( $i=0; $i<=6; ++$i ) {
			$unseen[$i] = wls_get_unseen_entry_count( $i );
		}
	}
	
/*	if( $show_new_info ) {
		?>
	
		<h3><?php _e( 'New entries', WLS_TEXTDOMAIN ); ?></h3>
		<p>
			<?php
				printf( __( 'You have currently %d new log entries of severity %s or more.', WLS_TEXTDOMAIN ),
					$unseen[$min_severity], '<code>'.wls_severity_to_string( $min_severity ).'</code>'
				);
				if( $unseen[$min_severity] > $log_entries_per_page ) {
					echo ' ';
					printf( __( 'Showing oldest %d.', WLS_TEXTDOMAIN ), $log_entries_per_page );
				}
			?>
		</p>
		<?php
	}*/
	?>
		<form method="post">
			<?php
				foreach( $args as $name => $val ) {
					echo '<input type="hidden" name="'.$name.'" value="'.$val.'" />';
				}
			?>
			<label><?php _e( 'Severity filter: ', WLS_TEXTDOMAIN ); ?>&nbsp;</label>
			<?php
				for( $i=0; $i<=5; ++$i ) {
					$severity = wls_severity_to_string( $i );
					if( $show_unread_info && $unseen[$i] > 0 ) {
						$severity.= ' ('.$unseen[$i].')';
					}
					$style = wls_category_to_style( $i );
					if( $i == $min_severity && !isset( $_REQUEST['entry_id'] ) ) {
						$style.= 'border-width: medium; border-color: black; ';
					}
					?>
					<input type="submit" name="switch_severity" value="<?php echo $severity ?>" style="<?php echo $style; ?>" />
					<?php
				}
			?>
		</form>
		<br />
	<?php
	
	return $min_severity;
}


function wls_superadmin_overview_page_view( $log_id ) {

	extract( wls_get_settings() );
	
	$log = wls_get_log( $log_id );
	
	?>
	<div class="wrap">
		<h2><?php printf( __( 'Log category: %s', WLS_TEXTDOMAIN ), $log->log_name ); ?></h2>
		<p><?php echo $log->description; ?></p>
		<?php
			/* Show severity filter and return it's current setting. */
			$min_severity = wls_show_severity_filter( array( 'wls_action' => 'view', 'id' => $log_id ), false );
		?>
		<form method="get">
			<input type="hidden" name="page" value="wls-superadmin-overview" />
			<input type="hidden" name="wls_action" value="view" />
			<input type="hidden" name="id" value="<?php echo $log_id; ?>" />
			<?php
				$table = new WlsAdminOverviewTable();
				$table->prepare_items( $log_id, true, $min_severity );
				$table->display();
							
			?>
		</form>
	</div>
	<?php
}


function wls_severity_to_string( $severity ) {
	switch( $severity ) {
	case 1:
		return 'debug';
	case 2:
		return 'notice';
	case 3:
		return 'warning';
	case 4:
		return 'error';
	case 5:
		return 'fatal';
	case 0:
	default:
		return 'none';
	}
}


function wls_string_to_severity( $s ) {
	if( $s == 'debug' )
		return 1;
	else if( $s == 'notice' )
		return 2;
	else if( $s == 'warning' )
		return 3;
	else if( $s == 'error' )
		return 4;
	else if( $s == 'fatal' )
		return 5;
	else
		return 0;
}


function wls_category_to_style( $category ) {
	switch( $category ) {
	case 1:
		return '';
	case 2:
		return 'color:green;';
	case 3:
		return 'color:orange;font-weight:bold;';
	case 4:
		return 'color:red; font-weight:bold;';
	case 5:
		return 'font-weight:bold; color:white; background-color:red;';
	case 0:
	default:
		return '';
	}
}

/* Remove _wp_http_referer */
add_action( "init", "wls_remove_http_referer" );

function wls_remove_http_referer() {
	if ( isset( $_REQUEST["page"] )
			&& $_REQUEST["page"] == "wls-superadmin-overview"
			&& isset( $_GET['_wp_http_referer'])
			&& !empty( $_GET['_wp_http_referer'] ) ) {
		wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
		exit;
	}
}

?>
