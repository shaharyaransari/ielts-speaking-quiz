<?php
namespace ISQNS\Result;
use \ISQNS\Quiz\Helpers;
use \ISQNS\Base\BaseHelpers;
use \ISQNS\External\Whisper;
use \ISQNS\External\GrammerAPI;
use \ISQNS\External\OpenAI;
use \ISQNS\External\PronunciationAPI;
use \ISQNS\Result\ResultsManager;
use \WP_REST_Server;

class AjaxHooks{
    public function register(){
        add_action('wp_ajax_get_openai_vocab_suggestions', array($this, 'get_openai_vocab_suggestions'));
        add_action('wp_ajax_nopriv_openai_vocab_suggestions', array($this, 'get_openai_vocab_suggestions'));     
        add_action('wp_ajax_get_vocabulary_score', array($this, 'get_vocabulary_score'));
        add_action('wp_ajax_nopriv_get_vocabulary_score', array($this, 'get_vocabulary_score'));     
        add_action('wp_ajax_get_grammer_score', array($this, 'get_grammer_score'));
        add_action('wp_ajax_nopriv_get_grammer_score', array($this, 'get_grammer_score'));     
        add_action('wp_ajax_get_pronunciation_data', array($this, 'get_pronunciation_data'));
        add_action('wp_ajax_nopriv_get_pronunciation_data', array($this, 'get_pronunciation_data'));     
        add_action('wp_ajax_save_result', array($this, 'save_result'));
        add_action('wp_ajax_nopriv_save_result', array($this, 'save_result'));     
    }

    public function get_pronunciation_data(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'use_pronunciation_nonce' )){
  
         if(isset($_POST['transcript']) && $_POST['transcript'] != '' && (isset($_POST['audio_url']))){
            $transcript = stripslashes($_POST['transcript']);
            $audio_url = $_POST['audio_url'];
            $data = PronunciationAPI::get_pronunciation_data($audio_url, $transcript);
            $data = array($data);
            wp_send_json_success(  $data );
         }else{
            wp_send_json_error('Incomplete Request');
         }

      }else{
         wp_send_json_error('Un Authorized Access');
      }
    }
    public function get_openai_vocab_suggestions(){
        if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'use_ielts_openai' )){
  
           if(isset($_POST['transcript']) && $_POST['transcript'] != ''){
              $transcript = $_POST['transcript'];
              $suggestions = OpenAI::get_vocabulary_suggestions($transcript);
              wp_send_json_success(  $suggestions );
           }else{
              wp_send_json_error('Incomplete Request');
           }

        }else{
           wp_send_json_error('Un Authorized Access');
        }
     }
   
   public function get_vocabulary_score(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'use_ielts_openai' )){
  
         if(isset($_POST['transcript']) && $_POST['transcript'] != ''){
            $transcript = $_POST['transcript'];
            $score = OpenAI::get_vocabulary_score($transcript);
            wp_send_json_success(  $score );
         }else{
            wp_send_json_error('Incomplete Request');
         }

      }else{
         wp_send_json_error('Un Authorized Access');
      }
   }
   public function get_grammer_score(){
      if(isset($_REQUEST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'use_ielts_openai' )){
  
         if(isset($_POST['transcript']) && $_POST['transcript'] != ''){
            $transcript = $_POST['transcript'];
            $errors = $_POST['errors'];
            $score = OpenAI::get_grammer_score($transcript, $errors);
            wp_send_json_success(  $score );
         }else{
            wp_send_json_error('Incomplete Request');
         }

      }else{
         wp_send_json_error('Un Authorized Access');
      }
   }

   public function save_result(){
      if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'save_result_nonce' )){
         $user_id = $_POST['user_id'];
         $result = json_decode(stripslashes($_POST['result']),true);
         $try = $result['try_number'];
         $quiz = $result['quiz_id'];
         $result['result_ready'] = true;
         $result_id = ResultsManager::add_result($user_id, $quiz, $result, $try);
         if($result_id){
            wp_send_json_success($result);
         }else{
            wp_send_json_error($result_id); 
         }
      }else{
         wp_send_json_error( 'Unauthorized Access' );
      }
   }
}