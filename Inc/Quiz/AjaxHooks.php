<?php
namespace ISQNS\Quiz;
use \ISQNS\Quiz\Helpers;
use \ISQNS\External\Whisper;
use \ISQNS\External\GrammerAPI;
use \ISQNS\External\OpenAI;
use \ISQNS\Result\ResultsManager;
use ISQNS\LearnDash\Quiz as LearndashQuizHelper;
use \WP_REST_Server;

// WP_REST_Server::READABLE = ‘GET’

// WP_REST_Server::EDITABLE = ‘POST, PUT, PATCH’

// WP_REST_Server::DELETABLE = ‘DELETE’

// WP_REST_Server::ALLMETHODS = ‘GET, POST, PUT, PATCH, DELETE’
class AjaxHooks {
   public function register(){
    // We will need to get Quiz Data speaking part and question
    // We will need to update User Meta to store results object
   //  add_action('wp_ajax_load_ielts_question', array($this, 'load_ielts_question'));
   //  add_action('wp_ajax_load_ielts_speaking_part', array($this, 'load_ielts_speaking_part'));
   //  add_action('wp_ajax_submit_answer', array($this, 'submit_answer'));
   //  add_action('wp_ajax_get_sp_allowed_time', array($this, 'get_sp_allowed_time'));
   
   add_action('rest_api_init', array($this, 'register_routs'));
   add_action('wp_ajax_get_audio_transcript', array($this, 'get_audio_transcript'));
   add_action('wp_ajax_nopriv_get_audio_transcript', array($this, 'get_audio_transcript'));
   add_action('wp_ajax_get_grammer_corrections', array($this, 'get_grammer_corrections'));
   add_action('wp_ajax_nopriv_get_grammer_corrections', array($this, 'get_grammer_corrections'));
   add_action('wp_ajax_get_improved_answer', array($this, 'get_improved_answer'));
   add_action('wp_ajax_nopriv_get_improved_answer', array($this, 'get_improved_answer'));
    add_action('wp_ajax_nopriv_submit_quiz', array($this, 'submit_quiz'));
    add_action('wp_ajax_submit_quiz', array($this, 'submit_quiz'));
    add_action('wp_ajax_upload_audio_file', array($this, 'upload_audio_file'));
    add_action('wp_ajax_nopriv_upload_audio_file', array($this, 'upload_audio_file'));
    add_action('wp_ajax_delete_question_audio', array($this, 'delete_question_audio'));
    add_action('wp_ajax_nopriv_delete_question_audio', array($this, 'delete_question_audio'));
}

   function register_routs(){
      // Rout for Getting Question Data 
      register_rest_route( 'ielts-speaking-quiz/v2', '/question/(?P<id>\d+)' , [
         'methods'  => WP_REST_Server::READABLE,
         'callback' => [ $this, 'get_question_data' ],
         'permission_callback' => '__return_true',
         'args' => [
            'id' => [
              /** Return true or false. */
              'validate_callback' => function( $value, $request, $param ) {
                return true;
              },
          
              /** Sanitization allows you to clean the parameters. It happens AFTER validation. */
              'sanitize_callback' => function( $value, $request, $param ) {
                return intval( $value );
              }
            ],
          ]
       ]);

       // Rout For Getting Speaking Part Data
       register_rest_route( 'ielts-speaking-quiz/v2', '/speaking-part/(?P<id>\d+)' , [
         'methods'  => WP_REST_Server::READABLE,
         'callback' => [ $this, 'get_speaking_part_data' ],
         'permission_callback' => '__return_true',
         'args' => [
            'id' => [
              /** Return true or false. */
              'validate_callback' => function( $value, $request, $param ) {
                return true;
              },
          
              /** Sanitization allows you to clean the parameters. It happens AFTER validation. */
              'sanitize_callback' => function( $value, $request, $param ) {
                return intval( $value );
              }
            ],
          ]
       ]);

      // Rout for Updating User Data 
      // register_rest_route( 'ielts-speaking-quiz/v2', '/user/' );
   }

   function get_question_data($request){
      $parameters = $request->get_params();
      $question = get_post($parameters['id']);
      $response = array(
         'ID' => $question->ID,
         'post_author' => intVal($question->post_author),
         'post_content' => $question->post_content,
         'post_title' => $question->post_title,
      );

      $meta = get_post_meta($parameters['id'], 'question_audio',true);
      $response['question_audio'] = $meta;
      return $response;
   }

   function get_speaking_part_data($request){
      $parameters = $request->get_params();
      $speaking_part = get_post($parameters['id']);
      $response = array(
         'ID' => $speaking_part->ID,
         'post_author' => intVal($speaking_part->post_author),
         'post_content' => $speaking_part->post_content,
         'post_title' => $speaking_part->post_title,
      );

      $time = intVal(get_post_meta($parameters['id'], 'allowed_rec_time', true)) * 60 ; // Converting into Seconds
      $response['allowed_rec_time'] = $time;
      return $response;
   }

   function get_audio_transcript(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'get_audio_transcript' )){
         if(isset($_FILES['recorded_audio'])){
            $file = $_FILES['recorded_audio'];
            $fileData = Helpers::uploadFile($file);
            $audio_url = $fileData['url'];
            $transcript = Whisper::get_transcript($audio_url);
            $fileData['transcript'] = $transcript;
            wp_send_json_success($fileData);
         }else{
            wp_send_json_error( 'No File Given with request');
         }
      }else{
         wp_send_json_error( 'Invalid Request');
      }
   }

   function upload_audio_file(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'] , 'upload_file_nonce' )){
         if(isset($_FILES['file'])){
            $fileData = Helpers::uploadFile($_FILES['file']);
            wp_send_json_success($fileData);
         }
      }else{
         wp_send_json_error('Un Authorized Access');
      }
   }

   public function get_grammer_corrections(){
      if(isset($_POST['transcript'])){
          $correction_data = GrammerAPI::get_corrections($_POST['transcript']);
          $transcript = $_POST['transcript'];
          $data = array(
              'corrections' => $correction_data,
              'transcript' => $transcript
          );
          wp_send_json_success( $data );
      }else{
          wp_send_json_error( 'Transcript Required' );
      }
   }

   public function get_improved_answer(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'use_ielts_openai' )){

         if(isset($_POST['transcript']) && $_POST['transcript'] != ''){
            $transcript = $_POST['transcript'];
            $improved_answer = OpenAI::get_improved_answer($transcript);
            wp_send_json_success(  $improved_answer );
         }else{
            wp_send_json_error('Incomplete Request');
         }

      }else{
         wp_send_json_error('Un Authorized Access');
      }
   }

   public function submit_quiz(){
      if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'submit_ielts_quiz' )){
          $result_obj = json_decode( stripslashes($_POST['result_obj']) , true);
          $try_number = $result_obj['try_number'];
          $current_user = wp_get_current_user(  );
          $current_user_id = $current_user->ID;
          $quiz_id = $result_obj['quiz_id'];
          
          $attemted_quizzes = get_user_meta( $current_user_id, '_ielts_speaking_quizzes', true );
          if($attemted_quizzes && !empty($attemted_quizzes)){
            // Push the New Quiz ID to Existing Array
            array_push($attemted_quizzes, $quiz_id);
          }else{
            // Create a New Array with Current Quiz ID
            $attemted_quizzes = array($quiz_id);
          }
          update_user_meta(  $current_user_id , '_ielts_speaking_quizzes' , $attemted_quizzes );
          $result_id = ResultsManager::add_result($current_user_id, $quiz_id, $result_obj, $try_number);
          $result_url = ResultsManager::get_quiz_result_page_url($quiz_id,$try_number,$current_user_id);
          $data = array(
              'user' => $current_user_id,
              'result_url' => $result_url,
          );

          if(function_exists('learndash_process_mark_complete')){
            $ld_quiz_id = isset($result_obj['ld_quiz']) ? $result_obj['ld_quiz'] : null;
            $ld_course = isset($result_obj['ld_course']) ? $result_obj['ld_quiz'] : null;
            if($ld_course && $ld_quiz_id){
               $data['quiz_data']  = LearndashQuizHelper::autocomplete_ld_quiz($current_user_id, $ld_quiz_id, $ld_course, $quiz_id, $try_number);
               $data['user_meta']  = get_user_meta( $current_user_id, '_sfwd-quizzes', true);
            }
          }

          wp_send_json_success($data);
      }else{
          wp_send_json_error( 'Invalid Request' );
      }
   }

   public function delete_question_audio(){
      if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'delete_audio_nonce' )){
         if(isset($_POST['attachment_id'])){
            $deleted = wp_delete_attachment($_POST['attachment_id'],true);
            if($deleted){
               wp_send_json_success("Audio Deleted");
            }else{
               wp_send_json_error("Something Went Wrong");
            }
         }
      }
   }
}