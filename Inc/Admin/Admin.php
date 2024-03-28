<?php
namespace ISQNS\Admin;
use \ISQNS\Base\SettingsAPI;
use \ISQNS\Admin\Helpers;

// Initialize Admin Interface of Plugin
class Admin {
    public $settings;
    public function register(){
        add_action('admin_menu', array($this, 'plugin_menu'));

        // Initialize Settings API Class
        $this->settings = new SettingsAPI();
        $this->set_settings();
        $this->set_sections();
        $this->set_fields();
        $this->settings->register();
    }

    
    public function plugin_menu(){
        $quiz_post_type_id = SpeakingQuizCPT::get_post_type_id();
        add_menu_page( 
            'IELTS Speaking Quizzes',
            'Speaking Quizzes',
            'manage_options',
            'edit.php?post_type='.$quiz_post_type_id,
            '',
            'dashicons-microphone',
            4
        );
        // add_submenu_page(
        //     'ielts_speaking_quiz',
        //     'Speaking Quizzes',
        //     'Speaking Quizzes',
        //     'manage_options',
        //     'edit.php?post_type='.$quiz_post_type_id,
        //     );

        add_submenu_page(
            'edit.php?post_type='.$quiz_post_type_id,
            'Quiz Questions',
            'Quiz Questions',
            'manage_options',
            'edit.php?post_type='.SpeakingQuizQuestionsCPT::get_post_type_id(),
            );
        add_submenu_page(
            'edit.php?post_type='.$quiz_post_type_id,
            'Speaking Parts',
            'Speaking Parts',
            'manage_options',
            'edit.php?post_type='.SpeakingPartsCPT::get_post_type_id(),
            );
            add_submenu_page( 
                'edit.php?post_type='.$quiz_post_type_id,
                'Settings',
                'Settings',
                'manage_options',
                'isq_settings',
                array($this, 'render_settings_page'),
            );
    }

    // Helper Function to Render Settings Page
    function render_settings_page(){ ?>
    <div class="wrap">
        <h1>Speaking Quiz Settings</h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php 
            settings_fields( 'Isq_option_group' ); 
            do_settings_sections( 'isq_settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php }

    // Helper Function To set Settings 
    public function set_settings(){
        $args = array(
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_quiz_builder_page',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_quiz_login_page',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_quiz_result_page',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_openai_token',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_lt_username',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_lt_api_key',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_whisper_token',
                    'args' => array()
                ),
                array(
                    'option_group' => 'Isq_option_group',
                    'option_name' => 'isq_pronun_key',
                    'args' => array()
                ),
            );
        $this->settings->set_settings($args);
    }
    // Helper Function To set Sections 
    public function set_sections(){
        $args = array(
                array(
                    'id' => 'Isq_pages_section',
                    'title' => 'Associate Pages',
                    'page' => 'isq_settings',
                    'args' => array()
                ),
                array(
                    'id' => 'Isq_apis_section',
                    'title' => 'Required Integrations',
                    'page' => 'isq_settings',
                    'args' => array()
                ),
            );
        $this->settings->set_sections($args);
    }
    // Helper Function To set Sections 
    public function set_fields(){
        $args = array(
                array(
                    'id' => 'isq_quiz_builder_page',
                    'title' => 'Quiz Builder',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_quiz_builder_page_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_pages_section',
                    'args' => array(
                        'label_for' => 'isq_quiz_builder_page'
                    )
                ),
                array(
                    'id' => 'isq_quiz_login_page',
                    'title' => 'Login Page',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_quiz_login_page_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_pages_section',
                    'args' => array(
                        'label_for' => 'isq_quiz_login_page'
                    )
                ),
                array(
                    'id' => 'isq_quiz_result_page',
                    'title' => 'Result Page',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_quiz_result_page_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_pages_section',
                    'args' => array(
                        'label_for' => 'isq_quiz_result_page'
                    )
                ),
                array(
                    'id' => 'isq_openai_token',
                    'title' => 'Open AI Token',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_openai_token_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_apis_section',
                    'args' => array(
                        'label_for' => 'isq_quiz_result_page'
                    )
                ),
                array(
                    'id' => 'isq_lt_username',
                    'title' => 'Language Tool Username',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_lt_username_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_apis_section',
                    'args' => array(
                        'label_for' => 'isq_lt_username'
                    )
                ),
                array(
                    'id' => 'isq_lt_api_key',
                    'title' => 'Language Tool API Key',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_lt_api_key_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_apis_section',
                    'args' => array(
                        'label_for' => 'isq_lt_api_key'
                    )
                ),
                array(
                    'id' => 'isq_whisper_token',
                    'title' => 'Whisper Auth Token',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_whisper_token_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_apis_section',
                    'args' => array(
                        'label_for' => 'isq_whisper_token'
                    )
                ),
                array(
                    'id' => 'isq_pronun_key',
                    'title' => 'Pronunciation Key',
                    'callback' => array('\ISQNS\Admin\Helpers', 'isq_pronun_key_cb'),
                    'page' => 'isq_settings',
                    'section' => 'Isq_apis_section',
                    'args' => array(
                        'label_for' => 'isq_pronun_key'
                    )
                ),
                
            );
        $this->settings->set_fields($args);
    }
}