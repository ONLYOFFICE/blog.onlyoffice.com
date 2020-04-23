<?php
class YOP_Poll_Other_Answers {
    private static $errors_present = false,
                $case_sensitive = false;
    public static function add( $poll_id, $element_id, $vote_id, $answer ) {
        if ( true === isset ( $poll_id ) && ( $poll_id > 0 ) ) {
            if ( true === isset( $vote_id ) && ( $vote_id > 0 ) ) {
                $data = array(
                    'poll_id' => $poll_id,
                    'element_id' => $element_id,
                    'vote_id' => $vote_id,
                    'answer' => trim( $answer ),
                    'status' => 'active',
                    'added_date' => current_time( 'mysql' ),
                );
                $query_result = $GLOBALS['wpdb']->insert( $GLOBALS['wpdb']->yop_poll_other_answers, $data );
                if ( false !== $query_result ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public static function get_for_element( $element_id ) {
        $query = "SELECT `answer`, COUNT(*) AS `total_submits` FROM {$GLOBALS['wpdb']->yop_poll_other_answers} WHERE `element_id` = %d AND `status` = 'active' GROUP BY `answer`";
        $other_answers = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare( $query, $element_id ) );
        return $other_answers;
    }
}