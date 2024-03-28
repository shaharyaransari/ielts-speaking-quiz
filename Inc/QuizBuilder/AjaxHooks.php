<?php
namespace ISQNS\QuizBuilder;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Admin\SpeakingPartsCPT as Part;
use \ISQNS\Admin\SpeakingQuizQuestionsCPT as Question;
use \ISQNS\Base\CPTBase;
use \ISQNS\QuizBuilder\Helpers;
class AjaxHooks {
    public function register(){
        // Save Quiz Actions
        add_action('wp_ajax_publish_s_quiz', array($this, 'publish_speaking_quiz'));
        add_action('wp_ajax_draft_s_quiz', array($this, 'draft_speaking_quiz'));
        add_action('wp_ajax_publish_speaking_part', array($this, 'publish_speaking_part'));
        add_action('wp_ajax_publish_speaking_q', array($this, 'publish_speaking_question'));
        add_action('wp_ajax_search_ielts_cpt', array($this, 'handle_builder_search'));
        add_action('wp_ajax_delete_ielts_post', array($this, 'delete_ielts_post'));
    }

    public function publish_speaking_quiz(){
        $this->save_speaking_quiz('publish');
        // wp_send_json_success('Fine');
    }
    public function draft_speaking_quiz(){
        $this->save_speaking_quiz('draft');
    }

    public function publish_speaking_part(){
        $this->save_speaking_part('publish');
    }
    public function publish_speaking_question(){
        $this->save_speaking_question('publish');
    }

    public function delete_ielts_post(){
        wp_send_json_success( 'Fine' );
    }

    /**
     * @param $status Quiz New status to save possible values are 'draft', 'publish'
     */
    private function save_speaking_quiz($status){
        if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'submit_speaking_quiz' )){
            $quiz_id = $_POST['quiz_id'];
            $quiz_obj = get_post($quiz_id);
            $quiz_desc = $_POST['quiz_desc'];
            $quiz_title = $_POST['quiz_title'];
            $contents = $_POST['quiz_contents_json'];
            $author = $_POST['author'];
            $args = [
                'ID' => $quiz_id,
                'post_title' => $quiz_title,
                'post_content' => $quiz_desc,
                'post_status' => $status,
                'post_author' => $author,
                'post_type' => Quiz::get_post_type_id(),
                ];

            if($quiz_obj){
                $contents = json_decode(stripcslashes($contents),true);
                $ld_quiz = null;
                if($quiz_obj->post_status === 'auto-draft'){
                    // User is creating Quiz First Time
                    // Need to add related LD quiz
                    $ld_quiz = Helpers::add_new_ld_quiz($args);
                    if($ld_quiz){
                        $args['meta_input']['ld_quiz_id'] = $ld_quiz;
                        $contents['ld_quiz_id'] = $ld_quiz;
                    }
                }elseif($quiz_obj->post_status == 'publish' || $quiz_obj->post_status == 'draft'){
                    $ld_quiz_id = get_post_meta( $quiz_obj->ID ,'ld_quiz_id', true );
                    $ld_quiz = get_post($ld_quiz_id);
                    if($ld_quiz_id && $ld_quiz && ($ld_quiz->post_status == 'draft' || $ld_quiz->post_status == 'publish')){
                        $ld_quiz = Helpers::update_ld_quiz($args, $ld_quiz_id);
                        $contents['ld_quiz_id'] = $ld_quiz;
                    }else{
                        $ld_quiz = Helpers::add_new_ld_quiz($args);
                        if($ld_quiz){
                            $args['meta_input']['ld_quiz_id'] = $ld_quiz;
                            $contents['ld_quiz_id'] = $ld_quiz;
                        }
                    }
                }
                $contents = json_encode($contents);
                
                $args['meta_input']['quiz_conents_json'] = $contents;
            }

            $id = wp_insert_post( $args );
            if($id){
                $response = [
                    'status' => 'success',
                    'quiz_conents_json' => $contents
                ];
                
                $contents = json_decode(stripcslashes($contents),true);
                // speaking Parts 
                foreach($contents['contents'] as $speaking_part){
                    $sp_id = $speaking_part['speaking_part_id'];
                    $display_order = $speaking_part['display_order'];
                    $questions = $speaking_part['questions'];
                    $added_locations = array(
                        'quiz_id' => $quiz_id,
                        'questions' => $questions,
                        'sp_id' => $sp_id,
                        'display_order' => $display_order,
                    );
                    // $old_locations = get_post_meta($sp_id,'added_locations',true);
                    // array_push($old_locations,$added_locations);
                    // Save Quiz Data in Speaking Part
                    update_post_meta( $sp_id, 'added_locations', $added_locations );
                }
                wp_send_json_success( $response, 200);
            }else{
                $response = [
                    'status' => 'error',
                    'msg' => 'Something Went Wrong'
                ];
                wp_send_json_error( $response, 200);
            }
        }
        wp_send_json_error( [
            'status' => 'error',
            'msg' => 'Invalid Request'
        ], 403);
    }

    /**
     * @param $status Speaking Part New status to save possible values are 'draft', 'publish'
     */
    private function save_speaking_part($status){
        if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'create_speaking_part' )){
            $sp_id = $_POST['sp_id'] ?? '0';
            $sp_desc = $_POST['sp_desc'];
            $sp_title = $_POST['sp_title'];
            $quiz_id = $_POST['quiz_id'];
            $allowed_rec_time = $_POST['allowed_rec_time'];
            // $contents = $_POST['sp_conents_json'];
            $author = $_POST['author'];
            $id = wp_insert_post( [
                'ID' => $sp_id,
                'post_title' => $sp_title,
                'post_content' => $sp_desc,
                'post_status' => $status,
                'post_author' => $author,
                'post_type' => Part::get_post_type_id(),
                'meta_input' => array(
                    'allowed_rec_time' => $allowed_rec_time,
                    'added_locations' => array(),
                )
            ]);
            if($id){
                $template = Helpers::get_new_sp_element($id,$quiz_id);
                $response = [
                    'status' => 'success',
                    'html' => $template,
                    'id' => $id
                ];
                wp_send_json_success( $response, 200);
            }else{
                $response = [
                    'status' => 'error',
                    'msg' => 'Something Went Wrong'
                ];
                wp_send_json_error( $response, 200);
            }
        }
        wp_send_json_error( [
            'status' => 'error',
            'msg' => 'Invalid Request'
        ], 403);
    }

    /**
     * @param $status Question New status to save possible values are 'draft', 'publish'
     */
    private function save_speaking_question($status){
        if(isset($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'create_ielts_speaking_question' )){
            $iq_id = $_POST['iq_id'] ?? '0';
            $iq_desc = $_POST['iq_desc'];
            $iq_title = $_POST['iq_title'];
            $quiz_id = $_POST['quiz_id'];
            $sp_id = $_POST['sp_id'];
            $audio_type = isset($_POST['question_audio_upload_type']) ? $_POST['question_audio_upload_type'] : 'no_audio_attached' ;
            if($audio_type == 'upload_audio_file'){
                if(!isset($_FILES['question_audio_file'])){
                    wp_send_json_error( 'Please Upload Valid Audio File' );
                }
                $audio_file = $_FILES['question_audio_file'];
                // Validations Needs to be done
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($audio_file, $upload_overrides);
                if ($movefile && !isset($movefile['error'])) {
                    $file_data = $movefile;
                    $audio_url = $file_data['url'];
                }else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    wp_send_json_error( $movefile['error']);
                }
            }elseif($audio_type == 'self_record_audio'){
                if(isset($_POST['question_audio_self']) && $_POST['question_audio_self'] != ''){
                    $audio_url = $_POST['question_audio_self'];
                }else{
                    if(!isset($_FILES['question_audio_self'])){
                        wp_send_json_error( 'Invalid Audio' );
                    }
                    $audio_file = $_FILES['question_audio_self'];
                    // Validations Needs to be done 
                    $upload_overrides = array('test_form' => false);
                    $movefile = wp_handle_upload($audio_file, $upload_overrides);
                    if ($movefile && !isset($movefile['error'])) {
                        $file_data = $movefile;
                        $audio_url = $file_data['url'];
                    }else {
                        /**
                         * Error generated by _wp_handle_upload()
                         * @see _wp_handle_upload() in wp-admin/includes/file.php
                         */
                        wp_send_json_error( $movefile['error']);
                    }
                }
            }elseif($audio_type == 'file_url'){
                $audio_url = $_POST['file_url']; // Validations Needs to be done
            }else{
                $audio_url = '';
            }
            $author = $_POST['author'];
            $id = wp_insert_post( [
                'ID' => $iq_id,
                'post_title' => $iq_title,
                'post_content' => $iq_desc,
                'post_status' => $status,
                'post_author' => $author,
                'post_type' => Question::get_post_type_id(),
                'meta_input' => array(
                    'question_audio' => [
                        'question_audio_upload_type' => $audio_type,
                        'audio_url' => $audio_url
                        ]
                )
            ]);
            if($id){
                $template = Helpers::get_new_iq_element($id,$quiz_id,$sp_id);
                $response = [
                    'status' => 'success',
                    'html' => $template,
                    'id' => $id,
                    'audio_url' => $audio_url
                ];
                wp_send_json_success( $response, 200);
            }else{
                $response = [
                    'status' => 'error',
                    'msg' => 'Something Went Wrong'
                ];
                wp_send_json_error( $response, 200);
            }
        }
        wp_send_json_error( [
            'status' => 'error',
            'msg' => 'Invalid Request'
        ], 403);
    }

    public function handle_builder_search(){
        if(isset($_POST['nonce']) && wp_verify_nonce( $_REQUEST['nonce'], 'isq_search_content' )&& isset($_POST['search_term']) && isset($_REQUEST['post_type'])){
            $search_term = $_REQUEST['search_term'];
            $post_type = $_REQUEST['post_type'];
            $quiz_id = $_REQUEST['quiz_id'];
            $response = array();
            $results = CPTBase::search($post_type,$search_term);
            
            if( ! $results ){
                wp_send_json_error( "Nothing Found try using a different search term" );
            }else{
                $count = count($results);
                // If a Quiz is Searched 
                if($post_type === Part::get_post_type_id()){
                    $template = 'Speaking Part(s) Found Template Not Done';
                    wp_send_json_success($template);
                }elseif($post_type === Question::get_post_type_id()){
                    
                    $sp_id = $_REQUEST['sp_id'];
                    if($sp_id){
                        $questions = [];
                        foreach($results as $iq){
                            $iq_id = $iq->ID;
                            $iq_label = $iq->post_title;
                            $iq_template = Helpers::get_new_iq_element($iq_id,$quiz_id,$sp_id);
                            $question_data = [
                                'id' => $iq_id,
                                'title' => $iq_label,
                                'element' => $iq_template
                            ];
                            array_push($questions, $question_data);
                        }
                        wp_send_json_success($questions);
                    }else{
                        $template = 'Speaking Part ID Not Supplied with Search Request';
                        wp_send_json_success($template);
                    }
                }
                wp_send_json_success($results);
            }

        }
        wp_send_json_error( 'Incomplete Request' );
    }


}