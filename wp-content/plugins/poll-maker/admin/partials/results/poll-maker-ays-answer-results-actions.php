<?php 
    class Poll_Answer_Results{
        private $plugin_name;

        public function __construct( $plugin_name ) {
            $this->plugin_name = $plugin_name;
        }

        // Get Polls
        public function get_polls(){
            global $wpdb;
            $polls_table = $wpdb->prefix."ayspoll_polls";
            $sql = "SELECT id,title FROM ".$polls_table;
            $result = $wpdb->get_results($sql , "ARRAY_A");
            return $result;
        }

        // Get answers
        public function get_poll_answers($id){
            global $wpdb;
            $answers_table = $wpdb->prefix."ayspoll_answers";
            $sql = "SELECT * FROM ".$answers_table." WHERE poll_id=".$id." AND show_user_added = 1";
            $result = $wpdb->get_results($sql , "ARRAY_A");
            return $result;
        }
    }
?>