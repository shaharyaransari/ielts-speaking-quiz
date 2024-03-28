<?php
namespace ISQNS\Base;
class BaseHelpers{

    public static function validate_ielts_user(){
        $login_page_url = self::get_ielts_login_page_url();
        // Check if current user is logged in or not 
        if( ! is_user_logged_in(  )){
            // Redirect User to login page 
            wp_redirect( $login_page_url );
        }
    }

    public static function get_ielts_login_page_url(){
        $login_page_id = get_option( 'isq_quiz_login_page' , null );
        if($login_page_id){
            $url = get_permalink( $login_page_id );
        }else{
            $url = get_home_url( );
        }
        return $url;
    }


}


