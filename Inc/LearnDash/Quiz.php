<?php
namespace ISQNS\LearnDash;

use ISQNS\Admin\SpeakingQuizCPT as SpeakingQuiz;
use ISQNS\Result\ResultsManager;

class Quiz {
    /**
     * Register hooks for the Quiz class.
     */
    public function register(){
        add_action('save_post', array($this, 'save_quiz'), 10, 3);
        add_action('learndash-quiz-after', array($this, 'print_speaking_quiz_info'));
        // add_filter('get_edit_post_link', array($this, 'ld_edit_quiz_link'), 10, 3);
        add_action('wp_trash_post', array($this, 'delete_linked_ld_quiz'), 10);
        add_action('before_delete_post', array($this, 'delete_linked_ld_quiz'), 10);
        add_filter('display_post_states', array($this, 'isq_display_states'), 10, 2);
    }

    /**
     * Add custom post states for speaking quizzes.
     */
    public function isq_display_states($post_states, $post) {
        if ($sp_quiz_id = get_post_meta($post->ID, 'ielts_sp_quiz_id', true)) {
            $post_states[] = 'Speaking Quiz';
        }
        return $post_states;
    }

    /**
     * Modify the edit quiz link for speaking quizzes.
     */
    public function ld_edit_quiz_link($url, $post_id, $context) {
        $quiz = get_post($post_id);
        if ($quiz->post_type == 'sfwd-quiz' && ($sp_quiz_id = get_post_meta($post_id, 'ielts_sp_quiz_id', true))) {
            $url = SpeakingQuiz::get_edit_url($sp_quiz_id);
        }
        return $url;
    }

    /**
     * Add custom fields to the LearnDash quiz settings.
     */
    public function add_fields_to_ld_quiz($setting_option_fields, $settings_metabox_key){
        if ($settings_metabox_key === 'learndash-quiz-access-settings' && !isset($setting_option_fields['ielts-sp-quiz-field'])) {
            $speaking_quizzes = SpeakingQuiz::get_speaking_quizzes();
            $setting_option_fields['ielts-sp-quiz'] = array(
                'name'      => 'ielts-sp-quiz-field',
                'label'     => sprintf(__('Select %s', 'learndash'), SpeakingQuiz::get_post_type_name()),
                'type'      => 'select',
                'class'     => '-medium',
                'value'     => get_post_meta($post->ID, 'ielts_sp_quiz_id', true) ?: '0',
                'default'   => '0',
                'options'   => $speaking_quizzes,
                'help_text' => sprintf(__('Select %s.', 'learndash'), SpeakingQuiz::get_post_type_name()),
            );
        }
        return $setting_option_fields;
    }

    /**
     * Print information about associated speaking quizzes.
     */
    public function print_speaking_quiz_info(){
        global $post;
        if ($speaking_quiz_id = get_post_meta($post->ID, 'ielts_sp_quiz_id', true)) {
            $course_id = learndash_get_course_id($post->ID);
            $quiz_url = get_permalink($speaking_quiz_id);
            $quiz_title = get_the_title($speaking_quiz_id);
            $quiz_desc = get_post_field('post_content', $speaking_quiz_id);
            if ($quiz_url) {
                $is_completed = isset($_REQUEST['completed']);
                ?>
                <div class="ielts-quiz-trigger-wrap">
                    <div class="ielts-quiz-desc-wrap">
                        <h2><?php echo __('Instructions', ISQ_TXT_DOMAIN); ?></h2>
                        <div class="ielts-quiz-desc-wrap-inner"><?php echo $quiz_desc; ?></div>
                        <?php
                        $action_text = $is_completed ? __('Quiz Completed Wanna Retry?', ISQ_TXT_DOMAIN) : __('Start Quiz', ISQ_TXT_DOMAIN);
                        $action_url = "$quiz_url?ld-quiz=$post->ID&course=$course_id";
                        ?>
                        <a href="<?php echo esc_url($action_url); ?>" target="blank" class="quiz-trigger quiz-action"><?php echo esc_html($action_text); ?></a>
                    </div>
                </div>
                <style>.wpProQuiz_content { display:none; }</style>
                <?php
            } else {
                echo __('Quiz Does not exist', ISQ_TXT_DOMAIN);
            }
        }
    }

    /**
     * Save quiz settings including associated speaking quiz.
     */
    public function save_quiz($post_id = 0, $post = null, $update = false){
        if (isset($_POST['learndash-quiz-access-settings']['ielts-sp-quiz-field'])) {
            update_post_meta($post_id, 'ielts_sp_quiz_id', sanitize_text_field($_POST['learndash-quiz-access-settings']['ielts-sp-quiz-field']));
        }
    }

    /**
     * Delete linked LearnDash quiz when associated speaking quiz is deleted.
     */
    public function delete_linked_ld_quiz($post_id){
        if ($post = get_post($post_id)) {
            ResultsManager::delete_results(['quiz_id' => $post_id]);
            if ($post->post_type === SpeakingQuiz::get_post_type_id()) {
                $linked_ld_quiz = get_post_meta($post_id, 'ld_quiz_id', true);
                wp_delete_post($linked_ld_quiz, true);
            }
        }
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