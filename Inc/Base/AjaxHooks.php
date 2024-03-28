<?php
namespace ISQNS\Base;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Base\TemplateLoader;
use \ISQNS\Base\BaseHelpers;
use \ISQNS\Base\Whisper;
use \ISQNS\Base\GrammerAPI;
use \ISQNS\Base\OpenAI;
class AjaxHooks {
    public function register(){
        // Quiz Archive Helpers
        add_action('wp_ajax_delete_ielts_quizzes', array($this, 'delete_ielts_quizzes'));
        add_action('wp_ajax_change_quiz_status', array($this, 'change_quiz_status'));
        add_action('wp_ajax_fetch_quizzes_list', array($this, 'fetch_quizzes_list'));
        add_action('wp_ajax_nopriv_fetch_quizzes_list', array($this, 'fetch_quizzes_list'));
    }

    // public function get_sp_allowed_time(){
    //     if(isset($_POST['quiz_elements'])){
    //         $quiz_elements = $_POST['quiz_elements'];
    //         $sp_index = $_POST['sp_index'];
    //         $quiz_elements = json_decode(stripslashes($quiz_elements),true);
    //         $sp_id = $quiz_elements[$sp_index]['speaking_part_id'];
    //         $time = get_post_meta($sp_id, 'allowed_rec_time', true);
    //         $time = intVal($time) * 60; // Converting Into Seconds
    //         wp_send_json_success($time); 
    //     }else{
    //         wp_send_json_error('Incomplete Request');
    //     }
    // }

    public function delete_ielts_quizzes(){
        if(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'],'delete_ielts_quizzes')){
            $quiz_ids = json_decode(stripcslashes($_POST['quizzes']));
            foreach ($quiz_ids as $quiz_id) {
                wp_delete_post( $quiz_id, true );
            }
            wp_send_json_success('Selected Quizzes Deleted');
        }else{
            wp_send_json_error('Something Went Wrong');
        }

    }

    public function fetch_quizzes_list(){
        if(isset($_POST['data'])){
            $data = json_decode(stripcslashes($_POST['data']),true);
            $author = get_current_user();
            $author_id = $author->ID;
            // var_dump($data);
            $args = array(
                'post_type' => Quiz::get_post_type_id(),
                'posts_per_page' => 10,
                'paged' => $data['page_number'],
                'author' => $author_id,
                'post_status' => array('draft', 'publish' ),
            );
            $query = new \WP_Query($args);
            $TemplateLoader =  new TemplateLoader();
            ob_start();
            if($query->have_posts(  )){
                while ($query->have_posts(  )){
                    $query->the_post();
                    $TemplateLoader->get_template_part('content', 'speaking-quiz' );
                }
            }else{
                echo "No Quizzes Found";
            }
            $html = ob_get_clean();
            $pagination =  paginate_links(array(
                'total'=>$query->max_num_pages,
                'current' => $data['page_number']
                )) ?? '';
            $found_posts = $query->found_posts;
            wp_send_json_success( ['html' => $html, 'pagination' => $pagination, 'found_posts'=> $found_posts]);
        }
        wp_send_json_error('Invalid Request');
    }
    public function change_quiz_status(){
        if(isset($_POST['new_status']) && isset($_POST['quiz_id'])){
                $post_id = wp_update_post(array(
                'ID'    =>  $_POST['quiz_id'],
                'post_status'   =>  $_POST['new_status'],
                ));

                if($post_id){
                    wp_send_json_success(array(
                        'quiz_id' => $post_id
                    ));
                }else{
                    wp_send_json_error("Something Went Wrong");
                }
        }else{
            wp_send_json_error('Incomplete Request');
        }
    }
}