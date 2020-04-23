<?php


/*****************************************************************************\
		ADMIN MENU
\*****************************************************************************/


add_action( 'network_admin_menu','wls_network_admin_menu' );


function wls_network_admin_menu() {
	/*if( !wls_is_network_mode() ) {
		return;
	}*/
	extract( wls_get_settings() );
	$unseen = wls_get_unseen_entry_count( $notification_severity_filter );
	if( $unseen > 0 ) {
		$unseen = ' <span id="awaiting-mod" class="update-plugins count-'.$unseen.'"><span class="comment-count">'.$unseen.'</span></span>';
	} else {
		$unseen = '';
	}
	add_submenu_page( 'index.php', __( 'WLS system logs', WLS_TEXTDOMAIN ), __( 'System logs', WLS_TEXTDOMAIN ).$unseen,
		'manage_network_options', 'wls-superadmin-overview', 'wls_superadmin_overview_page' );
	add_submenu_page( 'settings.php', __( 'Wordpress Logging Service', WLS_TEXTDOMAIN ), __( 'Wordpress Logging Service', WLS_TEXTDOMAIN ),
		'manage_network_options', 'wls-settings', 'wls_settings_page' );
}


add_action( 'admin_menu', 'wls_admin_menu' );


function wls_admin_menu() {
	if( wls_is_network_mode() || !current_user_can( 'manage_options' ) ) {
		return;
	}
	extract( wls_get_settings() );
	$unseen = wls_get_unseen_entry_count( $notification_severity_filter );
	if( $unseen > 0 ) {
		$unseen = ' <span id="awaiting-mod" class="update-plugins count-'.$unseen.'"><span class="comment-count">'.$unseen.'</span></span>';
	} else {
		$unseen = '';
	}
	add_submenu_page( 'index.php', __( 'WLS system logs', WLS_TEXTDOMAIN ), __( 'System logs', WLS_TEXTDOMAIN ).$unseen,
		'manage_options', 'wls-superadmin-overview', 'wls_superadmin_overview_page' );
	add_submenu_page( 'options-general.php', __( 'Wordpress Logging Service', WLS_TEXTDOMAIN ), __( 'Wordpress Logging Service', WLS_TEXTDOMAIN ),
		'manage_options', 'wls-settings', 'wls_settings_page' );
}


/*****************************************************************************\
		OPTIONS
\*****************************************************************************/


function wls_settings_page() {
	
	if( isset($_REQUEST['action']) ) {
        $action = $_REQUEST['action'];
    } else {
        $action = 'default';
    }
    
    switch( $action ) {
    case 'update-settings':
		wls_update_settings( $_POST['settings'] );
		wls_settings_page_default();
    	break;
    default:
    	wls_settings_page_default();
    }
}


function wls_settings_page_default() {
	
	extract( wls_get_settings() );
	
	?>
	<div class="wrap">
		<h2><?php _e( 'Wordpress Logging Service', WLS_TEXTDOMAIN ); ?></h2>
		<?php
			if( !$hide_donation_button ) {
				?>
				<h3><?php _e( 'Please consider a donation', WLS_TEXTDOMAIN ); ?></h3>
				<p>
					<?php _e( 'I spend quite a lot of my precious time working on opensource WordPress plugins. If you find this one useful, please consider helping me develop it further. Even the smallest amount of money you are willing to spend will be welcome.', WLS_TEXTDOMAIN ); ?>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="39WB3KGYFB3NA">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="border:none;" >
						<img style="display:none;" alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
				</p>
				<?php
			}
		?>
		<h3><?php _e( 'Basic settings', WLS_TEXTDOMAIN ); ?></h3>
        <form method="post">
            <input type="hidden" name="action" value="update-settings" />
            <table class="form-table">
                <tr valign="top">
                	<th>
                		<label><?php _e( 'Severity filter for notification in (network) admin menu', WLS_TEXTDOMAIN ); ?></label>
                	</th>
                	<td>
                		<input type="text" name="settings[notification_severity_filter]" value="<?php echo $notification_severity_filter; ?>" />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><?php _e( 'Default severity filter on overview page', WLS_TEXTDOMAIN ); ?></label>
                	</th>
                	<td>
                		<input type="text" name="settings[def_severity_filter]" value="<?php echo $def_severity_filter; ?>" />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><?php _e( 'WLS manager\'s ID', WLS_TEXTDOMAIN ); ?></label>
                	</th>
                	<td>
                		<input type="text" name="settings[wls_manager_id]" value="<?php echo $wls_manager_id; ?>" />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><?php _e( 'Log entries per page', WLS_TEXTDOMAIN ); ?></label>
                	</th>
                	<td>
                		<input type="text" name="settings[log_entries_per_page]" value="<?php echo $log_entries_per_page; ?>" />
                	</td>
                </tr>
                <tr valign="top">
                	<th>
                		<label><?php _e( 'Hide donation button', WLS_TEXTDOMAIN ); ?></label><br />
                	</th>
                	<td>
                		<input type="checkbox" name="settings[hide_donation_button]"
                			<?php if( $hide_donation_button ) echo 'checked="checked"'; ?>
                		/>
                	</td>
                	<td><small><?php _e( 'If you don\'t want to be bothered again...', WLS_TEXTDOMAIN ); ?></small></td>
                </tr>
			</table>
			<p class="submit">
	            <input type="submit" value="<?php _e( 'Save', WLS_TEXTDOMAIN ); ?>" />
	        </p>
		</form>
	</div>
	<?php

}

define( 'WLS_SETTINGS', 'wls_settings' );

function wls_get_settings() {
	$defaults = array(
		'def_severity_filter' => 0,
		'notification_severity_filter' => 2,
		'wls_manager_id' => 1,
		'log_entries_per_page' => 100,
		'hide_donation_button' => false
	);
	
	if( wls_is_network_mode() ) {
		$settings = get_site_option( WLS_SETTINGS, array() );
	} else {
		$settings = get_option( WLS_SETTINGS, array() );
	}
	
	return wp_parse_args( $settings, $defaults );
}


function wls_update_settings( $settings ) {
	if( wls_is_network_mode() ) {
		update_site_option( WLS_SETTINGS, $settings );
	} else {
		update_option( WLS_SETTINGS, $settings );
	}
}

?>
