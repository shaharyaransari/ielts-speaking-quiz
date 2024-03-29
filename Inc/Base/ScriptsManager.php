<?php
namespace ISQNS\Base;

use ISQNS\Admin\SpeakingQuizCPT as Quiz;
use ISQNS\Result\ResultsManager;

class ScriptsManager {
    /**
     * Register script enqueue actions.
     */
    public function register() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_general_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'single_quiz_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_template_scripts'));
    }

    /**
     * Enqueue general scripts for front-end.
     */
    public function enqueue_general_scripts() {
        wp_enqueue_style('isq-utility-styles', ISQ_DIR . 'Inc/assets/css/isq-utility-styles.css', array(), time());
        wp_enqueue_script('isq-general-script', ISQ_DIR . 'Inc/assets/js/ielts-speaking-quiz.js', array(), time(), true);
    }

    /**
     * Enqueue admin-specific scripts.
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style('isq-admin-styles', ISQ_DIR . 'Inc/assets/css/isq-admin-styles.css', array(), time());
    }

    /**
     * Enqueue template-specific scripts based on the page template.
     */
    public function enqueue_template_scripts() {
        global $post;
        $page_template_slug = get_page_template_slug($post->ID);

        if (in_array($page_template_slug, ['speaking-quiz-builder.php', 'speaking-quiz-list.php', 'speaking-quiz-result.php'])) {
            $css_file = '';
            $js_file = '';
            $localize_data = [];

            switch ($page_template_slug) {
                case 'speaking-quiz-builder.php':
                    $css_file = 'speaking-quiz-builder.css';
                    $js_file = 'speaking-quiz-builder.js';
                    $localize_data = ['ajaxurl' => admin_url('admin-ajax.php'), 'builderUrl' => get_permalink($post->ID)];
                    wp_enqueue_script('tiny_mce');
                    wp_enqueue_script('wp-tinymce');
                    
                    break;
                case 'speaking-quiz-list.php':
                    $css_file = 'speaking-quiz-archive.css';
                    $js_file = 'speaking-quiz-archive.js';
                    $localize_data = ['ajaxurl' => admin_url('admin-ajax.php')];
                    break;
                case 'speaking-quiz-result.php':
                    $css_file = 'speaking-quiz-result.css';
                    $js_file = 'speaking-quiz-result.js';
                    $localize_data = [
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'root' => esc_url_raw(rest_url()),
                        'nonce' => wp_create_nonce('wp_rest'),
                        'openai_nonce' => wp_create_nonce('use_ielts_openai'),
                        'pronunciation_nonce' => wp_create_nonce('use_pronunciation_nonce'),
                        'save_result_nonce' => wp_create_nonce('save_result_nonce'),
                        'plugin_dir' => ISQ_DIR,
                    ];
                    // Additional logic for result page
                    if (isset($_REQUEST['quiz']) && isset($_REQUEST['try']) && isset($_REQUEST['uid'])) {
                        $localize_data['result'] = ResultsManager::get_result(intVal($_REQUEST['try']), intVal($_REQUEST['uid']), intVal($_REQUEST['quiz']));
                        $localize_data['user_id'] = $_REQUEST['uid'];
                    }
                    break;
            }

            wp_enqueue_style($page_template_slug, ISQ_DIR . "Inc/assets/css/$css_file", array('isq-utility-styles'), time());
            wp_enqueue_script($page_template_slug, ISQ_DIR . "Inc/assets/js/$js_file", array('isq-general-script'), time(), true);
            wp_localize_script($page_template_slug, 'wpdata', $localize_data);
        }
    }

    /**
     * Enqueue scripts for shortcode.
     */
    public function enqueue_shortcode_scripts() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'speaking-quiz-list')) {
            wp_enqueue_style('speaking-quiz-list-shortcode', ISQ_DIR . 'Inc/assets/css/speaking-quiz-list-shortcode.css', array('isq-utility-styles'), time());
            wp_enqueue_script('speaking-quiz-list-shortcode', ISQ_DIR . 'Inc/assets/js/speaking-quiz-list-shortcode.js', array('isq-general-script'), time(), true);
            wp_localize_script('speaking-quiz-list-shortcode', 'wpdata', ['ajaxurl' => admin_url('admin-ajax.php')]);
        }
    }

    /**
     * Enqueue scripts for single quiz.
     */
    public function single_quiz_scripts() {
        global $post;
        if ($post->post_type == Quiz::get_post_type_id()) {
            wp_enqueue_script('speaking-quiz-single', ISQ_DIR . 'Inc/assets/js/speaking-quiz-single.js', array('isq-general-script'), time(), true);
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $try_number = ResultsManager::get_new_try_number($user_id, $post->ID);
            $quiz_elements = get_post_meta($post->ID, 'quiz_conents_json', true);
            $quiz_elements = $quiz_elements ? json_decode(stripslashes($quiz_elements), true)['contents'] : [];

            wp_localize_script('speaking-quiz-single', 'wpdata', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'quiz_elements' => $quiz_elements,
                'quiz_id' => $post->ID,
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'transcript_nonce' => wp_create_nonce('get_audio_transcript'),
                'openai_nonce' => wp_create_nonce('use_ielts_openai'),
                'upload_file_nonce' => wp_create_nonce('upload_file_nonce'),
                'try_number' => $try_number,
                'user_id' => $user_id,
                'delete_audio_nonce' => wp_create_nonce('delete_audio_nonce'),
            ]);

            wp_enqueue_style('speaking-quiz-single', ISQ_DIR . 'Inc/assets/css/speaking-quiz-single.css', array('isq-utility-styles'), time());
        }
    }
}