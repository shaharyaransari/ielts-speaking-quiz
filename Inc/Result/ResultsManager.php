<?php
namespace ISQNS\Result;

class ResultsManager {
    public static function add_result($user_id, $quiz_id, $result_obj, $try_number = 1) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';

        $data = array(
            'user_id' => intVal($user_id),
            'quiz_id' => intVal($quiz_id),
            'try_number' => intVal($try_number),
            'result_obj' => json_encode($result_obj)
        );
        // wp_send_json( $data );
        $format = array('%d', '%d', '%d', '%s');
        $exists = self::result_exists($user_id, $quiz_id, $try_number);

        if ($exists) { 
            $updated = $wpdb->update($table_name, array('result_obj' => $data['result_obj']), array('user_id' => $user_id, 'quiz_id' => $quiz_id, 'try_number' => $try_number), array('%s'), array('%d', '%d', '%d'));
            // wp_send_json( $updated );
            return $updated !== false ? $exists['id'] : $wpdb->last_error;
        } else {
            $inserted = $wpdb->insert($table_name, $data, $format);
            return $inserted !== false ? $wpdb->insert_id : $wpdb->last_error;
        }
    }

    private static function result_exists($user_id, $quiz_id, $try_number) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND quiz_id = %d AND try_number = %d", $user_id, $quiz_id, $try_number), ARRAY_A);
    }

    public static function get_results_by_quiz($quiz_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id = %d", $quiz_id), ARRAY_A);

        return self::decode_results($results);
    }

    public static function get_results_by_quiz_ids(array $quiz_ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        
        // Prepare the query part for the array of quiz IDs
        $placeholders = implode(',', array_fill(0, count($quiz_ids), '%d'));
        
        // Prepare the SQL query, injecting the table name and placeholders for quiz IDs
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id IN ($placeholders)", $quiz_ids);
        
        // Execute the query and fetch results
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return self::decode_results($results);
    }

    private static function decode_results($results) {
        if ($results) {
            foreach ($results as &$result) {
                $result['result_obj'] = json_decode($result['result_obj'], true);
            }
        }
        return $results;
    }

    public static function get_new_try_number($user_id, $quiz_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        $max_try_number = $wpdb->get_var($wpdb->prepare("SELECT MAX(try_number) FROM $table_name WHERE user_id = %d AND quiz_id = %d", $user_id, $quiz_id));
        return $max_try_number !== null ? intval($max_try_number) + 1 : 1;
    }

    public static function get_results_by_author($author_id) {
        $quizzes = get_posts(array(
            'author' => $author_id,
            'post_type' => \ISQNS\Admin\SpeakingQuizCPT::get_post_type_id(),
            'fields' => 'ids',
            'posts_per_page' => -1,
        ));

        $results = array();
        foreach ($quizzes as $quiz_id) {
            $quiz_results = self::get_results_by_quiz($quiz_id);
            $results = array_merge($results, $quiz_results);
        }

        return $results;
    }

    public static function get_results_by_group_leader($group_leader_id, $user_ids = array(), $group_ids = array()) {
        if(function_exists('groups_get_user_groups')){
            $groups = groups_get_user_groups($group_leader_id);
            // Get User IDs
            if(empty($user_ids) && empty($group_ids)){ // No User Ids given as Parameter
                // Build User_Ids Array
                $user_ids = [$group_leader_id];
                foreach ($groups['groups'] as $group_id) {
                    if (groups_is_user_admin($group_leader_id, $group_id)) { // false if not?
                        $group_members = groups_get_group_members(array('group_id' => $group_id))['members']; //
                        foreach ($group_members as $member) {
                            if (!in_array($member->ID, $user_ids)) { // Check if the user ID is not already in the array
                                $user_ids[] = $member->ID;
                            }
                        }
                    }
                }
            } // Let's complete this first
            $quiz_ids = [];
            foreach($user_ids as $user_id) {
                $attempted_quizzes = get_user_meta($user_id, '_ielts_speaking_quizzes', true);
                // Merge arrays
                $quiz_ids = array_merge($quiz_ids, $attempted_quizzes);
            }
            // Remove duplicates
            $quiz_ids = array_unique($quiz_ids);
            $results = self::get_results_by_quiz_ids($quiz_ids);
            return $results;
        }else{
            return 'The Function Requires BuddyBoss Theme to be Active';
        }
    }

    public static function get_results_by_user($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id), ARRAY_A);
        return self::decode_results($results);
    }
    public static function get_all_results() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        return self::decode_results($results);
    }

    public static function get_quiz_attempts_all_users($quiz_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id = %d", $quiz_id), ARRAY_A);
        return self::decode_results($results);
    }

    public static function get_quiz_attempts_of_user($quiz_id, $user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id = %d AND user_id = %d", $quiz_id, $user_id), ARRAY_A);
        return self::decode_results($results);
    }

    /**
     * Delete results based on provided arguments.
     *
     * @param array $args An array of arguments to specify which results to delete.
     *                    Supported arguments:
     *                    - 'result_id'  : Delete a result by its ID.
     *                    - 'user_id'    : Delete all results for a specific user.
     *                    - 'quiz_id'    : Delete all results for a specific quiz.
     *                    - 'try_number' : (Optional) Delete a specific attempt for a user and quiz.
     *
     * @return bool True on success, false on failure.
     */
    public static function delete_results($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';

        if (!empty($args['result_id'])) {
            // Delete result by result_id
            $deleted = $wpdb->delete($table_name, array('id' => $args['result_id']), array('%d'));
        } elseif (!empty($args['user_id']) && !empty($args['quiz_id'])) {
            if (!empty($args['try_number'])) {
                // Delete result by user_id, quiz_id, and try_number
                $deleted = $wpdb->delete($table_name, array(
                    'user_id' => $args['user_id'],
                    'quiz_id' => $args['quiz_id'],
                    'try_number' => $args['try_number']
                ), array('%d', '%d', '%d'));
            } else {
                // Delete all results by user_id and quiz_id
                $deleted = $wpdb->delete($table_name, array(
                    'user_id' => $args['user_id'],
                    'quiz_id' => $args['quiz_id']
                ), array('%d', '%d'));
            }
        } elseif (!empty($args['quiz_id'])) {
            // Delete all results by quiz_id
            $deleted = $wpdb->delete($table_name, array('quiz_id' => $args['quiz_id']), array('%d'));
        } elseif (!empty($args['user_id'])) {
            // Delete all results by user_id
            $deleted = $wpdb->delete($table_name, array('user_id' => $args['user_id']), array('%d'));
        } else {
            // No valid arguments provided
            return false;
        }

        return $deleted !== false;
    }

    public static function get_quiz_result_page_url($quiz_id,$try_number,$user_id){
        $query_args = array(
            'quiz' => $quiz_id,
            'try' => $try_number,
            'uid' => $user_id
        );
        $query = http_build_query($query_args);
        $result_nonce = wp_create_nonce('sp_quiz_result');
        $result_page_id = get_option( 'isq_quiz_result_page' , null );
        if($result_page_id){
            $result_page_url = get_permalink( $result_page_id );
        }else{
            $result_page_url = get_home_url( );
        }
        $result_page_url = "$result_page_url?$query";
        return $result_page_url ;
    }

    public static function get_result($try_number, $user_id, $quiz_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'isq_results';
    
        // Query the database to retrieve the result object
        $result_obj = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result_obj FROM $table_name WHERE try_number = %d AND user_id = %d AND quiz_id = %d",
                $try_number,
                $user_id,
                $quiz_id
            )
        );
        
        // If result object exists, decode it from JSON to PHP object
        if ($result_obj !== null) {
            $result =  json_decode($result_obj, true);
            return $result;
        } else {
            return null; // Return null if no result object found
        }
    }
}