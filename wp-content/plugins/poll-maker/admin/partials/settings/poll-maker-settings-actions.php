<?php
class Poll_Maker_Settings_Actions {
    private $plugin_name;

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        // $this->check_setting_mailchimp();
    }

    public function store_data($data){
        global $wpdb;
        $settings_table = $wpdb->prefix . "ayspoll_settings";
        if( isset($data["settings_action"]) && wp_verify_nonce( $data["settings_action"], 'settings_action' ) ){
            $success = 0;
            $roles = (isset($data['ays_user_roles']) && !empty($data['ays_user_roles'])) ? $data['ays_user_roles'] : array('administrator');
            $mailchimp_username = isset($data['ays_mailchimp_username']) ? $data['ays_mailchimp_username'] : '';
            $mailchimp_api_key = isset($data['ays_mailchimp_api_key']) ? $data['ays_mailchimp_api_key'] : '';
            $mailchimp = array(
                'username' => $mailchimp_username,
                'apiKey' => $mailchimp_api_key
            );

            $disable_ip_storing = isset($data['ays_disable_ip_storing']) && $data['ays_disable_ip_storing'] == 'on' ? $data['ays_disable_ip_storing'] : 'off';

            $answer_default_count = isset($data['ays_answer_default_count']) && !empty($data['ays_answer_default_count']) ? $data['ays_answer_default_count'] : 2;

            $answers_sound = isset($data['ays_poll_answers_sound']) ? $data['ays_poll_answers_sound'] : '';

            // Poll title length
            $poll_title_length = (isset($data['ays_poll_title_length']) && intval($data['ays_poll_title_length']) != 0) ? absint(intval($data['ays_poll_title_length'])) : 5;

            // Default Category for poll
            $ays_default_cat  = (isset($data['ays_poll_default_cat']) && !empty($data['ays_poll_default_cat'])) ? sanitize_text_field(implode("," , $data['ays_poll_default_cat'])) : "1";
            
            // Default Type for poll
            $ays_default_type = (isset($data['ays_poll_default_type']) && $data['ays_poll_default_type'] != "") ? sanitize_text_field($data['ays_poll_default_type']) : "choosing";

            // Poll expired and unpublished message
            $all_shortcode_message = isset($data['ays_poll_all_polls_shortcodes']) && $data['ays_poll_all_polls_shortcodes'] != '' ? sanitize_text_field($data['ays_poll_all_polls_shortcodes']) : '';

            // Show result view
            $poll_show_result_view = isset($data['ays_poll_show_result_view']) ? sanitize_text_field($data['ays_poll_show_result_view']) : 'standart';

            // Animation Top
            $poll_animation_top = (isset($data['ays_poll_animation_top']) && $data['ays_poll_animation_top'] != '') ? absint(intval($data['ays_poll_animation_top'])) : 100;
            $poll_enable_animation_top = (isset( $data['ays_poll_enable_animation_top'] ) && $data['ays_poll_enable_animation_top'] == 'on') ? 'on' : 'off';

            $options = array(
                "disable_ip_storing"    => $disable_ip_storing,
                "answer_default_count"  => $answer_default_count,
                "answers_sound"         => $answers_sound,
                "poll_title_length"     => $poll_title_length,
                "default_category"      => $ays_default_cat,
                "default_type"          => $ays_default_type,
                "all_shortcode_message" => $all_shortcode_message,
                "show_result_view"      => $poll_show_result_view,
                "poll_animation_top"        => $poll_animation_top,
                "poll_enable_animation_top" => $poll_enable_animation_top
            );

            $poll_fields_placeholder_name  = (isset($data['ays_poll_fields_placeholder_name']) && $data['ays_poll_fields_placeholder_name'] != '') ? stripslashes(sanitize_text_field( $data['ays_poll_fields_placeholder_name'] )) : 'Name' ;            
            $poll_fields_placeholder_email = (isset($data['ays_poll_fields_placeholder_email']) && $data['ays_poll_fields_placeholder_email'] != '') ? stripslashes(sanitize_text_field( $data['ays_poll_fields_placeholder_email'] )) : 'E-mail' ;
            $poll_fields_placeholder_phone = (isset($data['ays_poll_fields_placeholder_phone']) && $data['ays_poll_fields_placeholder_phone'] != '') ? stripslashes(sanitize_text_field( $data['ays_poll_fields_placeholder_phone'] )) : 'Phone' ;

            $fields_placeholders = array(
                'poll_fields_placeholder_name'   => $poll_fields_placeholder_name,
                'poll_fields_placeholder_email'  => $poll_fields_placeholder_email,
                'poll_fields_placeholder_phone'  => $poll_fields_placeholder_phone
            );

            $del_stat = "";
            $month_count = isset($data['ays_delete_results_by']) ? intval(sanitize_text_field($data['ays_delete_results_by'])) : null;
            if($month_count !== null && $month_count > 0){
                $year = intval( date( 'Y', current_time('timestamp') ) );
                $dt = intval( date( 'n', current_time('timestamp') ) );
                $month = $dt - $month_count;
                if($month < 0){
                    $month = 12 - $month;
                    if($month > 12){
                        $mn = $month % 12;
                        $mnac = ($month - $mn) / 12;
                        $month = 12 - ($mn);
                        $year -= $mnac;
                    }
                }elseif($month == 0){        
                    $month = 12;
                    $year--;
                }                
                $sql = "DELETE FROM " . esc_sql($wpdb->prefix) . "ayspoll_reports 
                        WHERE YEAR(vote_date) = '".esc_sql($year)."' 
                          AND MONTH(vote_date) <= '".esc_sql($month)."'";
                $res = $wpdb->query($sql);
                if($res >= 0){
                    $del_stat = "&del_stat=ok&mcount=".$month_count;
                }
            }

            $result = $this->ays_update_setting('mailchimp', json_encode($mailchimp));
            if ($result) {
                $success++;
            }

            $result = $this->ays_update_setting('options', json_encode($options));
            if($result){
                $success++;
            }

            $result = $this->ays_update_setting('fields_placeholders', json_encode($fields_placeholders,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            if ($result) {
                $success++;
            }

            $message = "saved";
            if($success > 0){
                $tab = "";
                if(isset($data['ays_poll_tab'])){
                    $tab = "&ays_poll_tab=".sanitize_text_field($data['ays_poll_tab']);
                }
                $url = admin_url('admin.php') . "?page=poll-maker-ays-settings" . $tab . '&status=' . $message.$del_stat;
                wp_redirect( esc_url_raw($url) );
            }
        }

    }

    public function get_db_data(){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "ayspoll_settings");
        $sql = "SELECT * FROM ".$settings_table;
        
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        if(count($results) > 0){
            return $results;
        }else{
            return array();
        }
    }

    public function check_setting_mailchimp(){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "ayspoll_settings");
        $mailchimp = esc_sql('mailchimp');
        $sql = "SELECT COUNT(*) FROM ".$settings_table." WHERE meta_key = %s";

        $result = $wpdb->get_var(
                    $wpdb->prepare( $sql, $mailchimp)
                  );

        if(intval($result) == 0){
            $this->ays_add_setting("mailchimp", "", "", "");
        }
        return false;
    }

    public function ays_get_setting($meta_key){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "ayspoll_settings");
        $key_meta = esc_sql($meta_key);

        $sql = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = %s";

        $result = $wpdb->get_var(
                    $wpdb->prepare( $sql, $key_meta)
                  );

        if($result != ""){
            return $result;
        }
        return false;
    }

    public function ays_add_setting($meta_key, $meta_value, $note = "", $options = ""){
        global $wpdb;
        $settings_table = $wpdb->prefix . "ayspoll_settings";
        $result = $wpdb->insert(
            $settings_table,
            array(
                'meta_key'    => $meta_key,
                'meta_value'  => $meta_value,
                'note'        => $note,
                'options'     => $options
            ),
            array( '%s', '%s', '%s', '%s' )
        );
        if($result >= 0){
            return true;
        }
        return false;
    }

    public function ays_update_setting($meta_key, $meta_value, $note = null, $options = null){
        global $wpdb;
        $settings_table = $wpdb->prefix . "ayspoll_settings";
        $value = array(
            'meta_value'  => $meta_value,
        );
        $value_s = array( '%s' );
        if($note != null){
            $value['note'] = $note;
            $value_s[] = '%s';
        }
        if($options != null){
            $value['options'] = $options;
            $value_s[] = '%s';
        }
        $result = $wpdb->update(
            $settings_table,
            $value,
            array( 'meta_key' => $meta_key, ),
            $value_s,
            array( '%s' )
        );
        if($result >= 0){
            return true;
        }
        return false;
    }

    public function ays_delete_setting($meta_key){
        global $wpdb;
        $settings_table = $wpdb->prefix . "ayspoll_settings";
        $wpdb->delete(
            $settings_table,
            array( 'meta_key' => $meta_key ),
            array( '%s' )
        );
    }


    public function poll_settings_notices($status){

        if ( empty( $status ) )
            return;

        if ( 'saved' == $status )
            $updated_message = esc_html( __( 'Changes saved.', $this->plugin_name ) );
        elseif ( 'updated' == $status )
            $updated_message = esc_html( __( 'Poll attribute .', $this->plugin_name ) );
        elseif ( 'deleted' == $status )
            $updated_message = esc_html( __( 'Poll attribute deleted.', $this->plugin_name ) );

        if ( empty( $updated_message ) )
            return;

        ?>
        <div class="notice notice-success is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }

}