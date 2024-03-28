<?php
namespace ISQNS\Quiz;

class Helpers {
    /**
     * $file should be an array element of $_FILES
     */
    public static function uploadFile($file){
        $success = true;
        $error = null;
        $attachment_id = null;
        $upload = null;

        $upload = wp_handle_upload( 
            $file,
            array( 'test_form' => false ) 
        );

        if( ! empty( $upload[ 'error' ] ) ){
            $success = false;
            $error = $upload['error'];
        }

        // it is time to add our uploaded image into WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $upload[ 'url' ],
                'post_mime_type' => $upload[ 'type' ],
                'post_title'     => basename( $upload[ 'file' ] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $upload[ 'file' ]
        );

        if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
            $success = false;
            $error = 'File Uploaded But Do not have any ID associated';
        }

        $response = array(
            'success' => $success,
            'error' => $error,
            'url' => $upload['url'],
            'attachment_id' => $attachment_id,
        );
        return $response;
    }


    public static function autocomplete_ld_quiz($user_id, $quiz_id, $course_id, $ielts_quiz_id, $try_number){
        // Get User meta of all User Quizzes
        $usermeta      = get_user_meta( $user_id, '_sfwd-quizzes', true );
        $quiz_progress = empty( $usermeta ) ? array() : $usermeta; // if usermeta is empty set empty array
        $quiz_changed  = false; // Simple flag to let us know we changed the quiz data so we can save it back to user meta.
        $quiz_new_status = 1;
        $quiz_meta = get_post_meta( $quiz_id, '_sfwd-quiz', true );

        if ( ! empty( $quiz_meta ) ) {
            // $quiz_old_status = ! learndash_is_quiz_notcomplete( $user_id, array( $quiz_id => 1 ), false, $course_id );

            // For Quiz if the admin marks a qiz complete we don't attempt to update an existing attempt for the user quiz.
            // Instead we add a new entry. LD doesn't care as it will take the complete one for calculations where needed.
            if ( (bool) true === (bool) $quiz_new_status ) {
                if ( (bool) true !== (bool) $quiz_old_status ) {

                    if ( isset( $quiz_meta['sfwd-quiz_lesson'] ) ) {
                        $lesson_id = absint( $quiz_meta['sfwd-quiz_lesson'] );
                    } else {
                        $lesson_id = 0;
                    }

                    if ( isset( $quiz_meta['sfwd-quiz_topic'] ) ) {
                        $topic_id = absint( $quiz_meta['sfwd-quiz_topic'] );
                    } else {
                        $topic_id = 0;
                    }

                    // If the admin is marking the quiz complete AND the quiz is NOT already complete...
                    // Then we add the minimal quiz data to the user profile.
                    $quizdata = array(
                        'quiz'                => $quiz_id,
                        'score'               => 0,
                        'count'               => 0,
                        'question_show_count' => 0,
                        'pass'                => true,
                        'rank'                => '-',
                        'time'                => time(),
                        'pro_quizid'          => absint( $quiz_meta['sfwd-quiz_quiz_pro'] ),
                        'course'              => $course_id,
                        'lesson'              => $lesson_id,
                        'topic'               => $topic_id,
                        'points'              => 0,
                        'total_points'        => 0,
                        'percentage'          => 0,
                        'timespent'           => 0,
                        'has_graded'          => false,
                        'statistic_ref_id'    => 0,
                        'm_edit_by'           => get_current_user_id(), // Manual Edit By ID.
                        'm_edit_time'         => time(), // Manual Edit timestamp.
                        'ielts_quiz_id'       => intval($ielts_quiz_id),
                        'ielts_quiz_try'       => intval($try_number),
                    );

                    $quiz_progress[] = $quizdata;

                    if ( true === $quizdata['pass'] ) {
                        $quizdata_pass = true;
                    } else {
                        $quizdata_pass = false;
                    }

                    // Then we add the quiz entry to the activity database.
                    learndash_update_user_activity(
                        array(
                            'course_id'          => $course_id,
                            'user_id'            => $user_id,
                            'post_id'            => $quiz_id,
                            'activity_type'      => 'quiz',
                            'activity_action'    => 'insert',
                            'activity_status'    => $quizdata_pass,
                            'activity_started'   => $quizdata['time'],
                            'activity_completed' => $quizdata['time'],
                            'activity_meta'      => $quizdata,
                        )
                    );

                    $quiz_changed = true;

                    if ( ( isset( $quizdata['course'] ) ) && ( ! empty( $quizdata['course'] ) ) ) {
                        $quizdata['course'] = get_post( $quizdata['course'] );
                    }

                    if ( ( isset( $quizdata['lesson'] ) ) && ( ! empty( $quizdata['lesson'] ) ) ) {
                        $quizdata['lesson'] = get_post( $quizdata['lesson'] );
                    }

                    if ( ( isset( $quizdata['topic'] ) ) && ( ! empty( $quizdata['topic'] ) ) ) {
                        $quizdata['topic'] = get_post( $quizdata['topic'] );
                    }

                    /**
                     * Fires after the quiz is marked as complete.
                     *
                     * @param array   $quizdata An array of quiz data.
                     * @param WP_User $user     WP_User object.
                     */
                    do_action( 'learndash_quiz_completed', $quizdata, get_user_by( 'ID', $user_id ) );

                }
            }
            if ( true === $quiz_changed ) {
                update_user_meta( $user_id, '_sfwd-quizzes', $quiz_progress );
            }
            learndash_process_mark_complete( $user_id, intval( $course_id ) );
			learndash_update_group_course_user_progress( intval( $course_id ) , $user_id, true );
            return $quizdata;
        }
    }
}