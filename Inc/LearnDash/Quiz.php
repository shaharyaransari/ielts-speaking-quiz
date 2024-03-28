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
}