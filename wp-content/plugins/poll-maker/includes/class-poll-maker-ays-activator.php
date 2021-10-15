<?php
global $ays_poll_db_version;
$ays_poll_db_version = '1.8.6';
/**
 * Fired during plugin activation
 *
 * @link       https://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Poll_Maker_Ays
 * @subpackage Poll_Maker_Ays/includes
 * @author     Poll Maker Team <info@ays-pro.com>
 */
class Poll_Maker_Ays_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$polls_table     = $wpdb->prefix . 'ayspoll_polls';
		$cats_table      = $wpdb->prefix . 'ayspoll_categories';
		$answers_table   = $wpdb->prefix . 'ayspoll_answers';
		$reports_table   = $wpdb->prefix . 'ayspoll_reports';
        $settings_table  = $wpdb->prefix . 'ayspoll_settings';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $polls_table (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                question TEXT NOT NULL,
                type VARCHAR(32) NOT NULL,
                view_type VARCHAR(64) NOT NULL,
                categories VARCHAR(255) NOT NULL,
                image TEXT DEFAULT '',
                show_title INT(1) DEFAULT 1,
                styles TEXT DEFAULT '',
                custom_css TEXT DEFAULT '',
                theme_id INT(5) DEFAULT 1,
                PRIMARY KEY (id)
            )$charset_collate;";
		dbDelta($sql);
		$sql = "CREATE TABLE $cats_table (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                options TEXT DEFAULT '',
                PRIMARY KEY (`id`)
            )$charset_collate;";
		dbDelta($sql);
		$sql = "CREATE TABLE $answers_table (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                poll_id INT(11) UNSIGNED NOT NULL,
                answer TEXT DEFAULT '',
                votes INT(11) NOT NULL,
                ordering INT(11) NOT NULL DEFAULT 1,
                redirect TEXT DEFAULT '',
                user_added INT(1) DEFAULT 0,
                show_user_added INT(1) DEFAULT 1,
                answer_img TEXT DEFAULT '',
                PRIMARY KEY (`id`)
            )$charset_collate;";
		dbDelta($sql);
		$sql = "CREATE TABLE $reports_table (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                answer_id INT(11) UNSIGNED NOT NULL,
                user_ip VARCHAR(128) NOT NULL,
                user_id INT(11) DEFAULT 0,
                vote_date DATETIME NOT NULL,
                unread  INT(1) DEFAULT 1,
                other_info  TEXT DEFAULT '',
                multi_answer_ids  TEXT DEFAULT '',
                PRIMARY KEY (`id`)
            )$charset_collate;";
        dbDelta($sql);
            $sql = "CREATE TABLE $settings_table (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `meta_key` TEXT NULL DEFAULT NULL,
                `meta_value` TEXT NULL DEFAULT NULL,
                `note` TEXT NULL DEFAULT NULL,
                `options` TEXT NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            )$charset_collate;";
		dbDelta($sql);
	}

	private static function insert_default_values() {
		global $wpdb;
		$answers_table = $wpdb->prefix . 'ayspoll_answers';
		$polls_table   = esc_sql($wpdb->prefix . 'ayspoll_polls');
		$cats_table    = esc_sql($wpdb->prefix . 'ayspoll_categories');
		$settings_table = esc_sql($wpdb->prefix . "ayspoll_settings");
		$cat_count     = $wpdb->get_var("SELECT COUNT(*) FROM ".$cats_table);

		if ($cat_count == 0) {
			$wpdb->insert($cats_table, 
				array(
					'title' => 'Uncategorized', 
					'description' => 'Default poll category'
				),
				array( '%s', '%s' )
			);
		}

		$polls_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$polls_table);

		if ($polls_count == 0) {
			$wpdb->insert($polls_table, 
				array(
				'title'       => 'Default choosing',
				'description' => 'Default choosing type ',
				'question'    => 'Did you like our plugin?',
				'type'        => 'choosing',
				'categories'  => ',1,',
				'styles'      => '{"main_color":"#0C6291","text_color":"#0C6291","icon_color":"#0C6291","icon_size":24,"width":0,"btn_text":"Vote","border_style":"ridge","bg_color":"#FBFEF9","answer_bg_color":"#FBFEF9","border_color":"#0C6291"}'
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s' )
			);
			$last_insert = $wpdb->insert_id;
			$wpdb->insert($answers_table, 
				array(
					'poll_id' => $last_insert, 
					'answer' => 'It was a mistake'
				),
				array( '%d', '%s' )
			);
			$wpdb->insert($answers_table, 
				array(
					'poll_id' => $last_insert, 
					'answer' => 'There was nothing special'
				),
				array( '%d', '%s' )
			);
			$wpdb->insert($answers_table, 
				array(
					'poll_id' => $last_insert, 
					'answer' => 'Everything\'s ok'
				),
				array( '%d', '%s' )
			);
			$wpdb->insert($answers_table, 
				array(
					'poll_id' => $last_insert, 
					'answer' => 'I enjoyed it'
				),
				array( '%d', '%s' )
			);
			$wpdb->insert($answers_table, 
				array(
					'poll_id' => $last_insert, 
					'answer' => 'It\'s amazing'
				),
				array( '%d', '%s' )
			);

			$wpdb->insert($polls_table, 
				array(
				'title'       => 'Default rating',
				'description' => 'Default rating type ',
				'question'    => 'Did you like our plugin?',
				'type'        => 'rating',
				'view_type'   => 'star',
				'categories'  => ',1,',
				'styles'      => '{"main_color":"#0C6291","text_color":"#0C6291","icon_color":"#0C6291","icon_size":24,"width":0,"btn_text":"Vote","border_style":"ridge","bg_color":"#FBFEF9","answer_bg_color":"#FBFEF9","border_color":"#0C6291"}'
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			); 
			$last_insert = $wpdb->insert_id;
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '1'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '2'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '3'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '4'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '5'),array( '%d', '%s' ));

			$wpdb->insert($polls_table,
				array(
					'title'       => 'Demographic poll',
					'description' => 'Demographic poll',
					'question'    => 'Where are You from?',
					'type'        => 'choosing',
					'categories'  => ',1,',
					'show_title'  => 1,
					'styles'      => '{"randomize_answers":"off","main_color":"#FBFEF9","text_color":"#FBFEF9","icon_color":"#FBFEF9","icon_size":24,"width":0,"btn_text":"Vote","border_style":"ridge","border_radius":"0","border_width":"","box_shadow_color":"","enable_box_shadow":"","bg_color":"#222222","answer_bg_color":"#222222","bg_image":false,"hide_results":0,"hide_results_text":"Thanks for your answer!","allow_not_vote":1,"show_social":1,"active_tab":"General","load_effect":"load_gif","load_gif":"plg_2","limit_users":0,"limitation_message":"","redirect_url":"","redirection_delay":0,"user_role":"","enable_restriction_pass":0,"restriction_pass_message":"","enable_logged_users":0,"enable_logged_users_message":"","notify_email_on":0,"notify_email":"","result_sort_type":"DESC","redirect_users":0,"redirect_after_vote_url":"","redirect_after_vote_delay":0,"published":1,"enable_pass_count":"on","activeInterval":"2019-05-30","deactiveInterval":"2019-05-30","active_date_message":"","active_date_check":"","enable_restart_button":1,"enable_vote_btn":0,"disable_answer_hover": 0,"border_color":"#FBFEF9"}',
					'theme_id'    => 2
				),
				array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d' )
			);
			$last_insert = $wpdb->insert_id;
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'Asia'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'Africa'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'Europe'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'North America'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'South America'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => 'Australia/Oceania'),array( '%d', '%s' ));
			$wpdb->insert($answers_table, array('poll_id' => $last_insert, 'answer' => '<b>Antarctica</b>'),array( '%d', '%s' ));
		}

		$metas = array(
            "mailchimp",
            "options",
			"fields_placeholders"
        );
        
        foreach($metas as $meta_key){
			$meta_val = esc_sql($meta_key);
			$sql = "SELECT COUNT(*) FROM ".$settings_table." WHERE meta_key = %s";
			$result = $wpdb->get_var(
	                    $wpdb->prepare( $sql, $meta_val)
	                  );
			if(intval($result) == 0){
		        $result = $wpdb->insert(
	                $settings_table,
	                array(
	                    'meta_key'    => $meta_val,
	                    'meta_value'  => "",
	                    'note'        => "",
	                    'options'     => ""
	                ),
	                array( '%s', '%s', '%s', '%s' )
	            );
	        }

		}
	}

	public static function ays_poll_update_db_check() {
		global $ays_poll_db_version;
		if (get_site_option('ays_poll_db_version') != $ays_poll_db_version) {
			self::activate();
			update_option('ays_poll_db_version', $ays_poll_db_version);
			self::insert_default_values();
		}
	}
}