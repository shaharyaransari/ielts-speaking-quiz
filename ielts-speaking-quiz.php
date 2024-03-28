<?php
/*
 * Plugin Name:       IELTS Speaking Quiz
 * Description:       Custom Plugin That Adds IELTS Speaking Quiz Functionality in Site Requires Learndash and Gravity Forms to be active
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shaharyar Ansari
 * Author URI:        https://shaharyar.wordpress4all.com/
 * Text Domain:       ielts-speaking-quiz
 */

// Restrict Direct Access 
if(!defined('ABSPATH')) die;

// Temporarily Disable warnings 
// error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

// Constants
define('ISQ_BASENAME',plugin_basename(__FILE__)); // unique name of current plugin
define('ISQ_PLUGIN_NAME','IELTS Speaking Quiz');
define('ISQ_PATH', plugin_dir_path( __FILE__ )); // use for require once
define('ISQ_DIR', plugin_dir_url( __FILE__ )); // use for enqueing
define('ISQ_TXT_DOMAIN', 'ielts-speaking-quiz'); // use for enqueing

// temp 
// define('BUILDER_PAGE_ID', 1650);
// define('LOGIN_PAGE_ID', 2109);
// define('SPEAKING_QUIZ_RESULT_PAGE', 2116);

// Autoload Classes Using Composer
if(file_exists(dirname(__FILE__).'/vendor/autoload.php')){
    require_once dirname(__FILE__).'/vendor/autoload.php';
}

// Initialize Classes 
if(class_exists('ISQNS\\Init')){
    ISQNS\Init::register_classes();
}
// Activation Process
function activate_isq_plugin(){
    ISQNS\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_isq_plugin');
