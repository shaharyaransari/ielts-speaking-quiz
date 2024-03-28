<?php
namespace ISQNS\Admin;
use \ISQNS\Base\Notice;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;

// Admin Helper Functions 
class Helpers {

    public function register(){
        add_action('plugins_loaded', array($this, 'run_required_plugins_check_with_notice'));

    }
    public static function get_required_plugins(){
        return [
                    [
                    'name'=> 'LearnDash',
                    'path' => 'sfwd-lms/sfwd_lms.php',
                    'url' => '#',
                    ],
                    // [
                    // 'name'=> 'Gravity Forms',
                    // 'path' => 'gravityforms/gravityforms.php',
                    // 'url' => '#',
                    // ]
                ];
    }


    public function run_required_plugins_check_with_notice(){
        foreach( self::get_required_plugins() as $plugin ){
            if(! in_array($plugin['path'], apply_filters('active_plugins', get_option('active_plugins')))){ 
               new Notice( ISQ_PLUGIN_NAME . ' Requires ' . $plugin['name'] . ' to be active to Work Properly', 'warning');
            }
        }
    }

    /**
     * A General Function To Check if All Required Plugins are Active or Not
     * @return Boolean True If all plugins are active otherwise false
     */
    public static function all_required_plugins_active(){
        $error = false;
        foreach( self::get_required_plugins() as $plugin ){
            if(! in_array($plugin['path'], apply_filters('active_plugins', get_option('active_plugins')))){ 
               $error = true;
            }
        }
        return !$error;
    }


    // Rending Setting Field from a Callback
    public static function isq_quiz_builder_page_cb(){ 
    ?>
    <select name="isq_quiz_builder_page"> 
        <option value="">
        <?php echo esc_attr( __( 'Select page' ) ); ?></option> 
        <?php 
        $pages = get_pages();
        $selected = get_option( 'isq_quiz_builder_page' , null);
        echo var_dump($selected);
        foreach ( $pages as $page ) {
            $is_selected = ($selected && $selected == $page->ID) ? 'selected' : ''; 
            $option = '<option value="' . $page->ID . '" '.$is_selected.' >';
            $option .= $page->post_title;
            $option .= '</option>';
            echo $option;
        }
        ?>
    </select>
    <?php }
    
    public static function isq_quiz_login_page_cb(){ 
    ?>
    <select name="isq_quiz_login_page"> 
        <option value="">
        <?php echo esc_attr( __( 'Select page' ) ); ?></option> 
        <?php 
        $pages = get_pages();
        $selected = get_option( 'isq_quiz_login_page' , null);
        echo var_dump($selected);
        foreach ( $pages as $page ) {
            $is_selected = ($selected && $selected == $page->ID) ? 'selected' : ''; 
            $option = '<option value="' . $page->ID . '" '.$is_selected.' >';
            $option .= $page->post_title;
            $option .= '</option>';
            echo $option;
        }
        ?>
    </select>
    <?php }

    public static function isq_quiz_result_page_cb(){ 
    ?>
    <select name="isq_quiz_result_page"> 
        <option value="">
        <?php echo esc_attr( __( 'Select page' ) ); ?></option> 
        <?php 
        $pages = get_pages();
        $selected = get_option( 'isq_quiz_result_page' , null);
        echo var_dump($selected);
        foreach ( $pages as $page ) {
            $is_selected = ($selected && $selected == $page->ID) ? 'selected' : ''; 
            $option = '<option value="' . $page->ID . '" '.$is_selected.' >';
            $option .= $page->post_title;
            $option .= '</option>';
            echo $option;
        }
        ?>
    </select>
    <?php }

    public static function isq_openai_token_cb(){ 
        $saved_token = get_option('isq_openai_token') ?? '';
        ?>
        <input type="text" name="isq_openai_token" id="isq_openai_token" value="<?php echo $saved_token; ?>">
    <?php }

    public static function isq_lt_api_key_cb(){ 
        $saved = get_option('isq_lt_api_key') ?? '';
        ?>
        <input type="text" name="isq_lt_api_key" id="isq_lt_api_key" value="<?php echo $saved; ?>">
    <?php }

    public static function isq_lt_username_cb(){ 
        $saved = get_option('isq_lt_username') ?? '';
        ?>
        <input type="text" name="isq_lt_username" id="isq_lt_username" value="<?php echo $saved; ?>">
    <?php }

    public static function isq_whisper_token_cb(){ 
        $saved = get_option('isq_whisper_token') ?? '';
        ?>
        <input type="text" name="isq_whisper_token" id="isq_whisper_token" value="<?php echo $saved; ?>">
    <?php }

    public static function isq_pronun_key_cb(){ 
        $saved = get_option('isq_pronun_key') ?? '';
        ?>
        <input type="text" name="isq_pronun_key" id="isq_pronun_key" value="<?php echo $saved; ?>">
    <?php }
}