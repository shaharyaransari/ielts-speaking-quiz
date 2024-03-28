<?php
namespace ISQNS\QuizBuilder;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Admin\SpeakingPartsCPT as Part;
use \ISQNS\Admin\SpeakingQuizQuestionsCPT as Question;
// Class Contains Quiz Builder Helper Functions 
class Helpers {

    public static function include_core_files(){
        require_once( ABSPATH . '/wp-admin/includes/post.php' );
    }

    public static function get_new_post_id($post_type){
        $default_post = get_default_post_to_edit($post_type,true);
        $new_post_id = $default_post->ID;
        return $new_post_id;
    }

    public static function render_title_edit_field($post_id, $placeholder = '', $default_value = '' ){ 
    ?>
    <div class="item-title-edit-container">
        <input type="text" name="item-title-<?php echo $post_id; ?>" id="item-title-<?php echo $post_id; ?>">
        <div class="title-actions">
            <button class="isp-mini-action-btn" data-post-id="<?php echo $post_id; ?>" >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M433.9 129.9l-83.9-83.9A48 48 0 0 0 316.1 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V163.9a48 48 0 0 0 -14.1-33.9zM272 80v80H144V80h128zm122 352H54a6 6 0 0 1 -6-6V86a6 6 0 0 1 6-6h42v104c0 13.3 10.7 24 24 24h176c13.3 0 24-10.7 24-24V83.9l78.2 78.2a6 6 0 0 1 1.8 4.2V426a6 6 0 0 1 -6 6zM224 232c-48.5 0-88 39.5-88 88s39.5 88 88 88 88-39.5 88-88-39.5-88-88-88zm0 128c-22.1 0-40-17.9-40-40s17.9-40 40-40 40 17.9 40 40-17.9 40-40 40z"/></svg>
                Click to Save
            </button>
        </div>
    </div>
    <?php 
    }

    /**
     * @param {request} $_REQUEST array
     * @return {Object | error_msg } Quiz Object on Success otherwise $error_msg contain.
     */
    public static function validate_and_get_data($request){
        // Quiz ID Parameter Name 
        $qid = 'qid';
        // Validating Request to prepare quiz data 
        $error_msg = '';
        if(isset($_REQUEST['nonce']) && isset($_REQUEST['action']) && wp_verify_nonce( $_REQUEST['nonce'], 'ielts_frontend_builder' )){

            $action = $_REQUEST['action'];
            if($action == 'edit'){

                // Validate Edit Request 
                if( isset($_REQUEST[$qid]) && ( get_post_type($_REQUEST[$qid])==Quiz::get_post_type_id() ) ){
                    $valid_request = true;
                    $quiz = get_post($_REQUEST[$qid]);
                    return $quiz;
                }else{
                    return 'Invalid Request - Post ID your Provided is Wrong';
                }

            }elseif($action == 'add-new'){
                
                // It is a Add New Request Prepare Variables
                $quiz = null;
                return $quiz;
                

            }else{
                $error_msg = 'Invalid Request - Your URL Parameters Seems to be Wrong Please Check URL Again.';
            }
        }else{
            $error_msg = 'Invalid Request - You are not allowed to access this section of our site';
        }
        return $error_msg;
    }

    public static function render_builder_contents($quiz_elements){
        $quiz_id = $quiz_elements['quiz_id'];
        $speaking_parts = $quiz_elements['contents'];
        foreach($speaking_parts as $sp){
            $sp_id = $sp['speaking_part_id'];
            $iq_ids = array();
            $questions = $sp['questions'];
            foreach($questions as $question){
                array_push($iq_ids, $question['question_id']);
            }
            echo self::get_new_sp_element($sp_id,$quiz_id,$iq_ids);
        }
    }

    /**
     * Creates a Speaking Part Element Ready to be added in Builder
     */
    public static function get_new_sp_element($sp_id,$quiz_id,$iq_ids = array(), $post_status='publish'){
        // Speaking Part Data 
        // Check if Post Exists
        if( ! get_post_type($sp_id) ) {
            return;
        }

        $sp = get_post($sp_id);
        
        if($sp->post_status != $post_status){
            return;
        }
        $title = $sp->post_title;
        $desc = $sp->post_content;
        $allowed_rec_time = get_post_meta( $sp_id, 'allowed_rec_time', true );
        $author = wp_get_current_user(  );

        ob_start();
        ?>
        <!-- Speaking Part  -->
        <div class="speaking-part sp-draggable ielts-draggable ielts-dropable" id="speaking-part-<?php echo $sp_id; ?>" data-quiz-id="<?php echo $quiz_id; ?>"  data-speaking-part-id="<?php echo $sp_id; ?>">

            <!-- Speaking Part Header  -->
            <div class="sp-header draggable-header">

                <!-- Icon  -->
                <div class="draggable-icon-area">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M278.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-64 64c-9.2 9.2-11.9 22.9-6.9 34.9s16.6 19.8 29.6 19.8h32v96H128V192c0-12.9-7.8-24.6-19.8-29.6s-25.7-2.2-34.9 6.9l-64 64c-12.5 12.5-12.5 32.8 0 45.3l64 64c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6V288h96v96H192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l64 64c12.5 12.5 32.8 12.5 45.3 0l64-64c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8H288V288h96v32c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l64-64c12.5-12.5 12.5-32.8 0-45.3l-64-64c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6v32H288V128h32c12.9 0 24.6-7.8 29.6-19.8s2.2-25.7-6.9-34.9l-64-64z"/></svg>
                </div>
                <!-- /Icon  -->

                <!-- Title  -->
                <div class="sp-title-area draggable-title-area">
                    <div class="sp-title draggable-title" id="sp_title_<?php echo $sp_id; ?>"><?php echo $title; ?></div>
                    <div class="sp-title-actions draggable-actions">
                        <button class="quiz-mini-action" onclick="deleteSpeakingPart()" data-speaking-part-id="<?php echo $sp_id; ?>" data-quiz-id="<?php echo $quiz_id; ?>" data-nonce="<?php echo wp_create_nonce('delete_ielts_post'); ?>">
                            Delete
                        </button>
                        <span class="expand-sp-icon expand-draggable-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"/></svg>
                        </span>
                    </div>
                </div>
                <!-- /Title  -->

            </div>
            <!-- /Speaking Part Header  -->

            <!-- Speaking Part Body  -->
            <div class="sp-body draggable-body">
                <div class="sp-body-inner draggable-body-inner">
                    <!-- Speaking Part Questions  -->
                    <div class="ielts-questions-container">
                                <!-- Question Actions  -->
                                <div class="draggable-final-actions sp-final-actions">

                                    <!-- ADD New Question  -->
                                    <div class="add-new-iq-container final-action">
                                        <div class="draggable-ptrigger add-new-iq-trigger">
                                            <button>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                                Create New Question
                                            </button>
                                        </div>
                                        <!-- .add-iq-popup Should Always be in Last relative to parent otherwise JS functinality Will Break  -->
                                        <div class="add-iq-popup draggable-item-popup">
                                            <!-- Close Button Should always be First Child  otherwise JS functinality Will Break-->
                                            <span class="close-item-popup">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                                            </span>
                                            <div class="draggable-item-popup-inner sp-item-popup-inner">

                                                <!-- Question Editing Options - "iq" in class names stands for ielts-question  -->
                                                <div class="iq-options">
                                                    <form id="add-iq" onsubmit="this.event.preventDefault();">
                                                        <!-- Title  -->
                                                        <div class="isq-field-group no-label">
                                                            <label for="iq_title"><?php echo __('Question Title', ISQ_TXT_DOMAIN); ?></label>
                                                            <input type="text" placeholder="<?php echo __('Question Title Here', ISQ_TXT_DOMAIN); ?>" required name="iq_title" id="iq_title">
                                                        </div>

                                                        <!-- Description  -->
                                                        <div class="isq-field-group">
                                                            <label for="iq_desc"><?php echo __('Question Instructions', ISQ_TXT_DOMAIN); ?></label>
                                                            <textarea name="iq_desc" id="iq_desc" cols="30" rows="4"></textarea>
                                                        </div>
                                                        <div class="question-audio-field isq-field-group c-display-container">
                                                            <select required class="c-display-trigger" name="question_audio_upload_type" id="question_audio_upload_type" onchange="toggleConditionalFields()">
                                                                <option value="no_audio_attached">No Audio</option>
                                                                <option value="upload_audio_file" >Upload Audio File</option>
                                                                <option value="self_record_audio" selected >Self Record </option>
                                                                <option value="file_url" >File URL</option>
                                                            </select>
                                                            <div class="c-display no_audio_attached">
                                                                <input type="hidden" name="no_audio" id="question_audio_file_<?php echo $iq_id; ?>" value="">
                                                            </div>
                                                            <!-- Upload Audio File  -->
                                                            
                                                            <div class="c-display upload_audio_file">
                                                                <input type="file" name="question_audio_file" id="question_audio_file"  value="">
                                                            </div>
                                                            <!-- /Upload Audio File  -->
                                                            <!-- Audio Self Record Section  -->
                                                            <div class="ielts-audio-actions c-display self_record_audio active">
                                                                <!-- Recorder  -->
                                                                <div class="recorder-module-wrap">
                                                                    <div class="recorder-module-inner-wrap">
                                                                        <div class="recorder-log">
                                                                            <span class="recording-active-msg">
                                                                                <span class="recording-dot"></span>
                                                                                Recording...
                                                                            </span>
                                                                        </div>
                                                                        <input type="file" name="question_audio_self">
                                                                        <div class="recorder-module">
                                                                            <canvas class="recorder-module-canvas"></canvas>
                                                                            <div class="recorder-module-trigger" onclick="doRecording()" data-file-name="your-audio">
                                                                                <div class="recorder-icon recorder-icon-start">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#12c99b" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                                                                </div>
                                                                                <div class="recorder-icon recorder-icon-stop">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>   
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Recorder  -->
                                                                <!-- New  -->
                                                                <div class="recording-preview-wrap">
                                                                    <div class="recording-preview">
                                                                        <div class="question-audio-wrap">
                                                                            <audio src="" controls controlsList="nodownload"></audio>
                                                                            <div class="question-audio-trigger" onclick="playQuestionAudio()" data-playing = "false">
                                                                                <span class="play-icon audio-icon">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"/></svg>
                                                                                </span>
                                                                                <span class="recording-icon audio-icon">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg>
                                                                                </span>
                                                                                <span class="pause-icon audio-icon">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"/></svg>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="recording-log">
                                                                        Start Recording to see preview
                                                                    </div>
                                                                </div>
                                                                <!-- /New  -->
                                                            </div>
                                                            <!-- /Audio Self Record  -->

                                                            <!-- Audio File URL -->
                                                            <div class="c-display file_url">
                                                                <?php if($audio_url && $audio_type == 'file_url'){ ?>
                                                                <input type="url" name="file_url" id="file_url_<?php echo $iq_id; ?>" placeholder="Add Your File URL" value="<?php echo $audio_url; ?>">
                                                                <div class="recording-preview">
                                                                <span>Old Recording Preview</span>
                                                                    <audio preload="true" src="<?php if($audio_url) echo $audio_url; ?>" controls controlsList="nodownload"></audio>
                                                                </div>
                                                                <?php }else{ ?>
                                                                    <input type="url" name="file_url" id="file_url_<?php echo $iq_id; ?>" placeholder="Add Your File URL" value="">
                                                                <?php } ?>
                                                            </div>
                                                            <!-- /Audio File URL -->

                                                        </div>
                                                        <div class="iq-actions-wrapper">
                                                            <input type="hidden" name="sp_id" id="sp_id" value="<?php echo $sp_id; ?>">
                                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                                                            <input type="hidden" name="iq_id" value="">
                                                            <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'create_ielts_speaking_question' ) ?>">
                                                            <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                                                            <button type="button" class="quiz-action" onclick="publishSpeakingQuestion()" data-speaking-part-id="<?php echo $sp_id; ?>"><?php echo __('Add Question', ISQ_TXT_DOMAIN); ?></button>
                                                        </div>

                                                    </form>
                                                </div>
                                                <!-- \Question Editing Options  -->

                                            </div>
                                        </div>
                                    </div>
                                    <!-- / ADD New Question  -->

                                    <!-- Load Existing Q  -->
                                    <div class="load-iq-container final-action">
                                        <div class="draggable-ptrigger load-iq-trigger">
                                            <button>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                                Load Existing Question
                                            </button>
                                        </div>
                                        <!-- Should Always be in Last otherwise JS functinality Will Break  -->
                                        <div class="load-iq-popup draggable-item-popup">
                                            <!-- Close Button Should always be First Child  otherwise JS functinality Will Break-->
                                            <span class="close-item-popup">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                                            </span>
                                            <div class="draggable-item-popup-inner load-iq-popup-inner">
                                                <form onsubmit="loadQuestions()" class="isq-inline-form" >

                                                        <div class="isq-field-group no-label">
                                                            <label for="search_term"><?php echo __('Search Question', ISQ_TXT_DOMAIN ); ?></label>
                                                            <input type="text" name="search_term" id="search_term" placeholder="<?php echo __('Search Question Title OR ID', ISQ_TXT_DOMAIN ); ?>">
                                                        </div>
                                                        <div class="iq-actions-wrapper">
                                                            <input type="hidden" name="sp_id" id="sp_id" value="<?php echo $sp_id; ?>">
                                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                                                            <input type="hidden" name="post_type" id="post_type" value="<?php echo Question::get_post_type_id(); ?>">
                                                            <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'isq_search_content' ) ?>">
                                                            <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                                                            <button class="quiz-action" data-speaking-part-id="<?php echo $sp_id; ?>"><?php echo __('Search Questions', ISQ_TXT_DOMAIN); ?></button>
                                                            <p>For Loading All Questions Just Hit Search Button without adding any value in search field</p>
                                                        </div>
                                                </form>
                                                <!-- Should Always in Last of Parent Container Otherwise JS functionality will break  -->
                                                <div class="isq-ajax-search-results">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- / Load Existing Q  -->

                                    <!-- Speaking Part Settings -->
                                    <div class="sp-setting-container final-action">
                                        <div class="draggable-ptrigger sp-setting-trigger">
                                            <button>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/></svg>
                                                Speaking Part Settings
                                            </button>
                                        </div>
                                        <!-- Should Always be in Last otherwise JS functinality Will Break  -->
                                        <div class="sp-setting-popup draggable-item-popup">
                                            <!-- Close Button Should always be First Child  otherwise JS functinality Will Break-->
                                            <span class="close-item-popup">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                                            </span>
                                            <div class="draggable-item-popup-inner sp-setting-popup-inner">
                                                <!-- Speaking Part Editing Options  -->
                                                <div class="speaking-part-options">
                                                    <form id="edit-speaking-part-<?php echo $sp_id;?>" onsubmit="this.event.preventDefault();">
                                                        <!-- Title  -->
                                                        <div class="isq-field-group no-label">
                                                            <label for="sp_title"><?php echo __('Speaking Part Title', ISQ_TXT_DOMAIN); ?></label>
                                                            <input type="text" placeholder="<?php echo __('Speaking Part Title Here', ISQ_TXT_DOMAIN); ?>" required name="sp_title" id="sp_title" value="<?php echo $title; ?>" oninput="modifyTargetTxt('sp_title_<?php echo $sp_id; ?>')">
                                                        </div>

                                                        <!-- Description  -->
                                                        <div class="isq-field-group">
                                                            <label for="sp_desc"><?php echo __('Speaking Part Instructions', ISQ_TXT_DOMAIN); ?></label>
                                                            <textarea name="sp_desc" id="sp_desc" cols="30" rows="4"><?php echo $desc; ?></textarea>
                                                        </div>

                                                        <!-- Time Field  -->
                                                        <div class="isq-field-group">
                                                            <label for="allowed_rec_time">Total Recording Time Allowed (Minutes)</label>
                                                            <input required type="number" name="allowed_rec_time" id="allowed_rec_time" value="<?php echo $allowed_rec_time; ?>" >
                                                        </div>

                                                        <div class="sp-actions-wrapper">
                                                            <input type="hidden" name="sp_id" id="sp_id" value="<?php echo $sp_id;?>">
                                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                                                            <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'create_speaking_part' ) ?>">
                                                            <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                                                            <button type="button" class="quiz-action" onclick="updateSpeakingPart()"><?php echo __('Update Settings', ISQ_TXT_DOMAIN); ?></button>
                                                        </div>

                                                    </form>
                                                </div>
                                                <!-- \Speaking Part Editing Options  -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- / Speaking Part Settings  -->
                                </div>
                                <!-- /Question Actions  -->

                                <!-- Questions List  -->
                                <div class="ielts-questions ielts-draggables" draggable="false" data-speaking-part-id="<?php echo $sp_id; ?>" data-quiz-id="<?php echo $quiz_id; ?>">
                                <?php if(!empty($iq_ids)){
                                    foreach ($iq_ids as $iq_id) {
                                        echo self::get_new_iq_element($iq_id,$quiz_id,$sp_id);
                                    }
                                } ?>
                                </div>
                                <!-- /Questions List  -->
                        
                            </div>
                            <!-- /Part Questions  -->

                </div>
            </div>
            <!-- /Speaking Part Body  -->

        </div>
        <!-- /Speaking Part  -->
        <?php
        return ob_get_clean();
    }

    /**
     * Creates a Speaking Part Element Ready to be added in Builder
     */
    public static function get_new_iq_element($iq_id,$quiz_id,$sp_id){
        // Speaking Part Data 
        $iq = get_post($iq_id);
        $title = $iq->post_title;
        $desc = $iq->post_content;
        $date = date("Y-m-d h:i:sa");
        $audio_title = str_replace(' ', '-',strtolower("$title-$iq_id") );
        $author = wp_get_current_user(  );
        $audio_data = get_post_meta($iq_id,'question_audio')[0];
        $audio_type = $audio_data['question_audio_upload_type'];
        $audio_url = $audio_data['audio_url'];
        ob_start();
        ?>
        <!-- Single IELTS Question  -->
        <div class="ielts-question iq-draggable ielts-draggable" id="ielts-question-<?php echo $iq_id; ?>"   data-question-id="<?php echo $iq_id; ?>" data-quiz-id ="<?php echo $quiz_id; ?>" data-speaking-part-id = "<?php echo $sp_id; ?>">             
            <!-- Question Header  -->
            <div class="iq-header draggable-header">

                <!-- Icon  -->
                <div class="draggable-icon-area">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M278.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-64 64c-9.2 9.2-11.9 22.9-6.9 34.9s16.6 19.8 29.6 19.8h32v96H128V192c0-12.9-7.8-24.6-19.8-29.6s-25.7-2.2-34.9 6.9l-64 64c-12.5 12.5-12.5 32.8 0 45.3l64 64c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6V288h96v96H192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l64 64c12.5 12.5 32.8 12.5 45.3 0l64-64c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8H288V288h96v32c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l64-64c12.5-12.5 12.5-32.8 0-45.3l-64-64c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6v32H288V128h32c12.9 0 24.6-7.8 29.6-19.8s2.2-25.7-6.9-34.9l-64-64z"/></svg>
                </div>
                <!-- /Icon  -->

                <!-- Title  -->
                <div class="iq-title-area draggable-title-area">
                    <div class="iq-title draggable-title" id="iq_title_<?php echo $iq_id;?>"><?php echo $title; ?></div>
                    <div class="iq-title-actions draggable-actions">
                        <button class="quiz-mini-action" onclick="deleteQuestion()" data-question-id="<?php echo $iq_id; ?>" data-speaking-part-id="<?php echo $sp_id; ?>" data-quiz-id="<?php echo $quiz_id; ?>" data-nonce="<?php echo wp_create_nonce('delete_ielts_post'); ?>">
                            Delete
                        </button>
                        <button class="quiz-mini-action" onclick="removeQuestion()" data-question-id="<?php echo $iq_id; ?>" data-speaking-part-id="<?php echo $sp_id; ?>" data-quiz-id="<?php echo $quiz_id; ?>">
                            Remove
                        </button>
                        <span class="expand-iq-icon expand-draggable-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"/></svg>
                        </span>
                    </div>
                </div>
                <!-- /Title  -->

            </div>
            <!-- /Question Header  -->

            <!-- Question Body  -->
            <div class="iq-body draggable-body">
                <div class="iq-body-inner draggable-body-inner">
                    <!-- Question Editing Options - "iq" in class names stands for ielts-question  -->
                    <div class="iq-options edit-iq-options">
                        <form onsubmit="this.event.preventDefault();">
                            <!-- Title  -->
                            <div class="isq-field-group no-label">
                                <label for="iq_title"><?php echo __('Question Title', ISQ_TXT_DOMAIN); ?></label>
                                <input type="text" placeholder="<?php echo __('Question Title Here', ISQ_TXT_DOMAIN); ?>" required name="iq_title" id="iq_title" value="<?php echo $title; ?>" oninput="modifyTargetTxt('iq_title_<?php echo $iq_id;?>')">
                            </div>

                            <!-- Description  -->
                            <div class="isq-field-group">
                                <label for="iq_desc"><?php echo __('Question Instructions', ISQ_TXT_DOMAIN); ?></label>
                                <textarea name="iq_desc" id="iq_desc" cols="30" rows="4"><?php echo $desc; ?></textarea>
                            </div>

                            <div class="question-audio-field isq-field-group c-display-container">
                                <select required class="c-display-trigger" name="question_audio_upload_type" id="question_audio_upload_type" onchange="toggleConditionalFields()">
                                    <option value="no_audio_attached" <?php if($audio_type == 'no_audio_attached') echo 'selected' ?>>No Audio</option>
                                    <option value="upload_audio_file" <?php if($audio_type == 'upload_audio_file') echo 'selected' ?>>Upload Audio File</option>
                                    <option value="self_record_audio" <?php if($audio_type == 'self_record_audio') echo 'selected' ?>>Self Record </option>
                                    <option value="file_url" <?php if($audio_type == 'file_url') echo 'selected' ?> >File URL</option>
                                </select>
                                <!-- Upload Audio File  -->
                                <div class="c-display no_audio_attached">
                                    <input type="hidden" name="no_audio" id="question_audio_file_<?php echo $iq_id; ?>" value="">
                                </div>
                                <div class="c-display upload_audio_file">
                                    <?php if($audio_url && $audio_type == 'upload_audio_file'){ ?>
                                        <input type="hidden" name="question_audio_file" id="question_audio_file_<?php echo $iq_id; ?>" value="<?php echo $audio_url; ?>">
                                        <div class="recording-preview">
                                        <span>Old Recording Preview</span>
                                        <audio preload="true" src="<?php if($audio_url) echo $audio_url; ?>" controls controlsList="nodownload"></audio>
                                    </div>
                                    <?php }else{ ?>
                                        <input type="file" name="question_audio_file" id="question_audio_file_<?php echo $iq_id; ?>" value="">
                                    <?php } ?>
                                </div>
                                <!-- /Upload Audio File  -->
                                <!-- Audio Self Record Section  -->
                                <div class="ielts-audio-actions c-display self_record_audio">
                                    <!-- Recorder  -->
                                    <div class="recorder-module-wrap">
                                        <div class="recorder-module-inner-wrap">
                                            <div class="recorder-log">
                                                <span class="recording-active-msg">
                                                    <span class="recording-dot"></span>
                                                    Recording...
                                                </span>
                                            </div>
                                            <?php if($audio_url && $audio_type == 'self_record_audio'){ ?>
                                                <input type="hidden" class="question_audio_self" name="question_audio_self" id="question_audio_self_<?php echo $iq_id; ?>" value="<?php echo $audio_url; ?>">
                                            <?php }else{ ?>
                                                <input type="file" class="question_audio_self" name="question_audio_self" id="question_audio_self_<?php echo $iq_id; ?>" value="">
                                            <?php } ?>
                                            <div class="recorder-module">
                                                <canvas class="recorder-module-canvas"></canvas>
                                                <div class="recorder-module-trigger" onclick="doRecording()" data-file-name="your-audio">
                                                    <div class="recorder-icon recorder-icon-start">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#12c99b" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                                    </div>
                                                    <div class="recorder-icon recorder-icon-stop">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>   
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Recorder  -->
                                    <!-- New  -->
                                    <div class="recording-preview-wrap">
                                        <div class="recording-preview">
                                            <div class="question-audio-wrap">
                                                <audio src="<?php if($audio_url){ echo $audio_url; }else{ echo ''; } ?>" controls controlsList="nodownload"></audio>
                                                <div class="question-audio-trigger" onclick="playQuestionAudio()" data-playing = "false">
                                                    <span class="play-icon audio-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"/></svg>
                                                    </span>
                                                    <span class="recording-icon audio-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg>
                                                    </span>
                                                    <span class="pause-icon audio-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"/></svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="recording-log">
                                            <?php if($audio_url){
                                                echo 'You have already recorded an audio. If you want to assign a new audio please record again';
                                            }else{
                                                echo 'Start Recording to see preview';
                                            } ?>
                                        </div>
                                    </div>
                                    <!-- /New  -->
                                </div>
                                <!-- /Audio Self Record  -->

                                <!-- Audio File URL -->
                                <div class="c-display file_url">
                                    <?php if($audio_url && $audio_type == 'file_url'){ ?>
                                    <input type="url" name="file_url" id="file_url_<?php echo $iq_id; ?>" placeholder="Add Your File URL" value="<?php echo $audio_url; ?>">
                                    <div class="recording-preview">
                                    <span>Old Recording Preview</span>
                                        <audio preload="true" src="<?php if($audio_url) echo $audio_url; ?>" controls controlsList="nodownload"></audio>
                                    </div>
                                    <?php }else{ ?>
                                        <input type="url" name="file_url" id="file_url_<?php echo $iq_id; ?>" placeholder="Add Your File URL" value="">
                                    <?php } ?>
                                </div>
                                <!-- /Audio File URL -->

                            </div>
                            <div class="iq-actions-wrapper">
                                <input type="hidden" name="sp_id" id="sp_id" value="<?php echo $sp_id; ?>">
                                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                                <input type="hidden" name="iq_id" value="<?php echo $iq_id;?>">
                                <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'create_ielts_speaking_question' ) ?>">
                                <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                                <!-- Should Not be Deep More then two Levels Others wise JS functionility will break  -->
                                <button type="button" class="quiz-action" onclick="updateSpeakingQuestion()" data-speaking-part-id="<?php echo $sp_id; ?>"><?php echo __('Update Question', ISQ_TXT_DOMAIN); ?></button>
                            </div>

                        </form>
                    </div>
                    <!-- \Question Editing Options  -->
                </div>
            </div>
            <!-- /Question Body  -->

        </div>
        <!-- /Single IELTS Question  -->
        <?php
        return ob_get_clean();
    }


    public static function add_proquiz_data_ld_quiz($new_ld_quiz_id ,$post_args){
        $quiz_mapper = new \WpProQuiz_Model_QuizMapper();
        $quiz_pro    = new \WpProQuiz_Model_Quiz();
        $quiz_pro->setName( $post_args['post_title'] );
        $quiz_pro->setText( 'AAZZAAZZ' ); // cspell:disable-line.
        $quiz_pro    = $quiz_mapper->save( $quiz_pro );
        $quiz_pro_id = $quiz_pro->getId();
        $quiz_pro_id = absint( $quiz_pro_id );
        learndash_update_setting( $new_ld_quiz_id, 'quiz_pro', $quiz_pro_id );
        // Set the 'View Statistics on Profile' for the new quiz.
        update_post_meta( $new_ld_quiz_id, '_viewProfileStatistics', 1 );
    }

    public static function add_new_ld_quiz($sp_quiz_array){
        $ld_quiz_id  = self::sync_ld_quiz($sp_quiz_array);
        return $ld_quiz_id;
    }

    public static function update_ld_quiz($sp_quiz_array, $ld_quiz_id){
        $ld_quiz_id  = self::sync_ld_quiz($sp_quiz_array, $ld_quiz_id);
        return $ld_quiz_id;
    }

    public static function sync_ld_quiz($sp_quiz_array, $ld_quiz_id = 0){
        $post_args = array(
            'ID' => $ld_quiz_id,
            'post_author' => $sp_quiz_array['post_author'],
            'post_title' => $sp_quiz_array['post_title'],
            'post_status' => $sp_quiz_array['post_status'],
            'post_type' => 'sfwd-quiz'
        );
        $quiz_id = wp_insert_post( $post_args );
        if($quiz_id){
            self::add_proquiz_data_ld_quiz($quiz_id ,$post_args);
            update_post_meta( $quiz_id, 'ielts_sp_quiz_id', $sp_quiz_array['ID'] );
        }
        return $quiz_id;
    }


}