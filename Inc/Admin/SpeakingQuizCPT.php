<?php
namespace ISQNS\Admin;
use \ISQNS\Base\CPTBase;
class SpeakingQuizCPT {

    private $post_type_name = "Speaking Quiz";
    public static $p_t_name = "Speaking Quiz";
    private static $post_type_id;

    public function register(){

        // Register Post Type
        new CPTBase($this->post_type_name,array('show_in_menu'=>false, 'has_archive' => true, 'rewrite' => array('slug' => 'ielts-speaking-quizzes')));

        // Generate ID - For Standardizing Ids are generated using post_type_name
        self::$post_type_id = CPTBase::uglify($this->post_type_name);

        // Add Custom Text Above Editor
        add_action( 'edit_form_after_title', array($this,'text_before_editor'));

        // Change Add New item URL
        add_filter( 'admin_url', array($this, 'change_add_new_link'), 10, 2 );
    }

    public static function get_edit_url($post_id){
        $nonce = wp_create_nonce( 'ielts_frontend_builder' );
        $builder_page_id = get_option( 'isq_quiz_builder_page' , null );
        if($builder_page_id){
            $builder_url = get_permalink( $builder_page_id ) . "?action=edit&qid=$post_id&nonce=$nonce";
        }else{
            $builder_url ='#builder-page-not-set';
        }
        return $builder_url;
    }

    public static function get_add_new_url(){
        $nonce = wp_create_nonce( 'ielts_frontend_builder' );
        $builder_page_id = get_option( 'isq_quiz_builder_page' , null );
        if($builder_page_id){
            $url = get_permalink( $builder_page_id ) . "?action=add-new&nonce=$nonce";
        }else{
            $url = '#builder-page-not-set';
        }
        return $url;
    }

    public function text_before_editor($post){
        if($post->post_type == self::$post_type_id){ 
        $nonce = wp_create_nonce( 'ielts_frontend_builder' );
        $builder_page_id = 1650; // Ideally Should Store Page ID
        $post_id = $post->ID;
        $quiz_builder_url = self::get_edit_url($post_id);
        echo "<div class='quiz-builder-button-wrap'>";
        echo "<a href='$quiz_builder_url'> Click to Load Quiz Builder </a>";
        echo "</div>";
        }
    }
    public static function get_post_type_id(){
        return self::$post_type_id;
    }

    public static function get_post_type_name(){
        return self::$p_t_name;
    }

    public function change_add_new_link($url,$path){
        $post_type_id = self::get_post_type_id();
        if( $path === 'post-new.php?post_type='.$post_type_id ) {
            $url = self::get_add_new_url();
        }
        return $url;
    }
}