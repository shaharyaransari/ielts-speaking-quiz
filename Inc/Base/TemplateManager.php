<?php
namespace ISQNS\Base;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Admin\SpeakingPartsCPT as Part;
use \ISQNS\Admin\SpeakingQuizQuestionsCPT as Question;
class TemplateManager{
    function register(){
        add_filter('single_template', array($this,'setup_isq_template'));
        add_filter('archive_template', array($this,'isq_archive_template'));
        // Add Custom Templates
        add_filter('theme_page_templates', array($this, 'register_templates'), 10, 3);
        add_filter('template_include', array($this, 'include_custom_templates'));

        // Add Learndash Templates
        add_filter('learndash_template', array($this, 'ld_templates_override'), 10, 5);
    }
    function setup_isq_template($template){

        $quiz_post_type = Quiz::get_post_type_id();
        $sp_post_type = Part::get_post_type_id();
        $question_post_type = Question::get_post_type_id();
        if(is_single()){
            $query_var = get_query_var('post_type');
            // Single Quiz 
            if ($query_var === $quiz_post_type ) {
                // Check If Template is Available in Theme Or not
                $templates = [
                    "single-$quiz_post_type.php",
                    "ielts-speaking-quiz/single-$quiz_post_type.php",
                    "ielts-speaking-quiz/templates/single-$quiz_post_type.php"
                ]; 
                $template = locate_template($templates);

                // If Not Load The Template File In Plugin 
                if (!$template) {
                    $template = ISQ_PATH. "Inc/templates/single-$quiz_post_type.php";
                }
            }
            // Single Speaking Part
            elseif ($query_var === $sp_post_type ) {
                // Check If Template is Available in Theme Or not
                $templates = [
                    "single-$sp_post_type.php",
                    "ielts-speaking-quiz/single-$sp_post_type.php",
                    "ielts-speaking-quiz/templates/single-$sp_post_type.php"
                ]; 
                $template = locate_template($templates);

                // If Not Load The Template File In Plugin 
                if (!$template) {
                    $template = ISQ_PATH. "Inc/templates/single-$sp_post_type.php";
                }
            }
            // Single Question
            elseif ($query_var === $question_post_type ) {
                // Check If Template is Available in Theme Or not
                $templates = [
                    "single-$question_post_type.php",
                    "ielts-speaking-quiz/single-$question_post_type.php",
                    "ielts-speaking-quiz/templates/single-$question_post_type.php"
                ]; 
                $template = locate_template($templates);

                // If Not Load The Template File In Plugin 
                if (!$template) {
                    $template = ISQ_PATH. "Inc/templates/single-$question_post_type.php";
                }
            }
        }

        

        return $template;
    }
    function isq_archive_template($template){
        $quiz_post_type = Quiz::get_post_type_id();
        $sp_post_type = Part::get_post_type_id();
        $question_post_type = Question::get_post_type_id();

        if (is_post_type_archive( $quiz_post_type )) {
            // Check If Template is Available in Theme Or not
            $templates = [
                "archive-$quiz_post_type.php",
                "ielts-speaking-quiz/archive-$quiz_post_type.php",
                "ielts-speaking-quiz/templates/archive-$quiz_post_type.php"
            ]; 
            $template = locate_template($templates);

            // If Not Load The Template File In Plugin 
            if (!$template) {
                $template = ISQ_PATH. "Inc/templates/archive-$quiz_post_type.php";
            }
        }elseif (is_post_type_archive( $sp_post_type )) {
            // Check If Template is Available in Theme Or not
            $templates = [
                "archive-$sp_post_type.php",
                "ielts-speaking-quiz/archive-$sp_post_type.php",
                "ielts-speaking-quiz/templates/archive-$sp_post_type.php"
            ]; 
            $template = locate_template($templates);

            // If Not Load The Template File In Plugin 
            if (!$template) {
                $template = ISQ_PATH. "Inc/templates/archive-$sp_post_type.php";
            }
        }elseif (is_post_type_archive( $question_post_type )) {
            // Check If Template is Available in Theme Or not
            $templates = [
                "archive-$question_post_type.php",
                "ielts-speaking-quiz/archive-$question_post_type.php",
                "ielts-speaking-quiz/templates/archive-$question_post_type.php"
            ]; 
            $template = locate_template($templates);

            // If Not Load The Template File In Plugin 
            if (!$template) {
                $template = ISQ_PATH. "Inc/templates/archive-$question_post_type.php";
            }
        }
        return $template;
    }
    
    public static function get_templates_list(){
        $templates = array(
            'speaking-quiz-builder.php' => 'Speaking Quiz Builder',
            'speaking-quiz-list.php' => 'Speaking Quiz List',
            'speaking-quiz-result.php' => 'Speaking Quiz Result',
        );
        return $templates;
    }

    public function register_templates($templates, $theme, $post){
        if($post->post_type == 'page'){
            foreach (self::get_templates_list() as $slug => $value) {
                $templates[$slug] = $value;
            }
        }
        return $templates;
    }

    public function include_custom_templates($template){
        global $post;

        $page_template_slug = get_page_template_slug( $post->ID );
        $our_templates = self::get_templates_list();

        // if Current Page Template is in Our Templates List 
        if(isset($our_templates[$page_template_slug])){
            $template = ISQ_PATH . 'Inc/templates/' . $page_template_slug;
        }

        return $template;
    }

    function ld_templates_override($filepath, $name, $args, $echo, $return_file_path) {
        if($name== 'quiz_progress_rows'){
            $path = ISQ_PATH.'Inc/templates/learndash/legacy/quiz_progress_rows.php';
            return $path;
        }else{
            return $filepath;
        }
    }

}