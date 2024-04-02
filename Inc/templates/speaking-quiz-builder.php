<?php
// Initial Security
if(!defined('ABSPATH')) die;

use ISQNS\QuizBuilder\Helpers;
use ISQNS\Admin\SpeakingQuizCPT as Quiz;
use ISQNS\Admin\SpeakingPartsCPT as Part;
use ISQNS\Admin\SpeakingQuizQuestionsCPT as Question;

// Include Required Core Files 
Helpers::include_core_files();

// Validate Request and prepare data
$data = Helpers::validate_and_get_data($_REQUEST);
if(is_string($data)){
     die($data); // Means Data Contains Error Message
}elseif( $data === null ){ // No Data exists create a new quiz
    // Default Values 
    $quiz=null;
    $quiz_id = Helpers::get_new_post_id(Quiz::get_post_type_id());
    $quiz_title = '';
    $quiz_desc = 'Quiz Description';
    $quiz_contents = [];
    $default_status = 'auto-draft';
}else{
    $quiz = $data; // Means data contain a quiz Post Object
    $quiz_id = $quiz->ID;
    $quiz_title = $quiz->post_title;
    $quiz_desc = $quiz->post_content;
    $quiz_contents = json_decode( get_post_meta( $quiz_id, 'quiz_conents_json', true), true );
    $default_status = $quiz->post_status;
}
// Check whether User is editing the quiz
$editMode = (isset($_REQUEST['qid'])  &&  $_REQUEST['action'] == 'edit');




// Current User Data
$author = wp_get_current_user(  );

// Addional Data
$new_nonce = wp_create_nonce('ielts_frontend_builder');

$add_new_url = Quiz::get_add_new_url();

// Include Theme Header
get_header(); ?>

<div class="speaking-quiz-builder-container">
    <div class="mini-notice-bar">
        <?php echo __('Changes Needs to be Saved In Quiz',ISQ_TXT_DOMAIN); ?>
    </div>
    <div class="notification-box"></div>
    <!-- Quiz Editing Options  -->
    <div class="speaking-quiz-options-container">
        <!-- Instructions Area  -->
        <div class="container-instructions">
            <div class="instructions-area">
                <h2><?php echo __('Quiz Options', ISQ_TXT_DOMAIN); ?></h2>
                <p><?php echo __('General Options For Quiz', ISQ_TXT_DOMAIN); ?></p>
            </div>
        </div>
        <!-- /Instructions Area  -->

        <!-- Quiz Editing Options  -->
        <div class="speaking-quiz-options">
            <form id="speaking-quiz-info" onsubmit="this.event.preventDefault();">
                <!-- Title  -->
                <div class="isq-field-group no-label">
                    <label for="quiz_title"><?php echo __('Quiz title', ISQ_TXT_DOMAIN); ?></label>
                    <input type="text" placeholder="<?php echo __('Quiz title Here', ISQ_TXT_DOMAIN); ?>" required name="quiz_title" id="quiz_title" value="<?php echo $quiz_title; ?>" oninput="showMiniNotice()">
                </div>

                <!-- Description  -->
                <div class="isq-field-group">
                    <!-- <label for="quiz_desc"><?php echo __('Quiz Instructions', ISQ_TXT_DOMAIN); ?></label> -->
                    <!-- <textarea id="quiz_desc"><?php echo $quiz_desc; ?></textarea> -->

                    <div class="advanced-editor">
                        <textarea name="quiz_desc" id="quiz_desc" cols="30" rows="4"><?php echo $quiz_desc; ?></textarea>
                        <div class="instructions-content">
                            <?php if(trim($quiz_desc) != false){
                            echo $quiz_desc;
                            }else{
                                echo 'No Content Added';
                            } ?>
                        </div>
                        <button role="button" onclick="enableAdvancedEditor()" data-enabled="false">Edit</button>
                    </div>
                </div>

                <div class="quiz-actions-wrapper">
                    <input type="hidden" name="quiz_id" id="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'submit_speaking_quiz' ) ?>">
                    <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                    <input type="hidden" name="quiz_default_status" id="quiz_default_status" value="<?php echo $default_status ?>">
                    <?php 
                    if($quiz){ ?>
                        <button type="button" class="quiz-action" onclick="saveSpeakingQuizDraft(false)" data-nonce="<?php echo $new_nonce; ?>" data-quiz-id="<?php echo $quiz_id; ?>"><?php echo __('Draft Quiz', ISQ_TXT_DOMAIN); ?></button>
                        <button type="button" class="quiz-action" onclick="publishSpeakingQuiz(false)" data-nonce="<?php echo $new_nonce; ?>" data-quiz-id="<?php echo $quiz_id; ?>">
                            <?php
                                echo __('Save Quiz', ISQ_TXT_DOMAIN);
                            ?>
                        </button>
                    <?php }else{ ?>
                        <button type="button" class="quiz-action" onclick="saveSpeakingQuizDraft(true)" data-nonce="<?php echo $new_nonce; ?>" data-quiz-id="<?php echo $quiz_id; ?>"><?php echo __('Draft Quiz', ISQ_TXT_DOMAIN); ?></button>
                        <button type="button" class="quiz-action" onclick="publishSpeakingQuiz(true)" data-nonce="<?php echo $new_nonce; ?>" data-quiz-id="<?php echo $quiz_id; ?>">
                            <?php
                                echo __('Save Quiz to Initialize Builder', ISQ_TXT_DOMAIN);
                            ?>
                        </button>
                    <?php } ?>
                    
                </div>

            </form>
        </div>
        <!-- \Quiz Editing Options  -->
    </div>
    <!-- /Quiz Editing Options  -->
    <?php if($editMode){ ?>
    <!-- Quiz Builder Start  -->
    <div class="ielts-speaking-quiz-builder" id="speaking-quiz-builder" data-quiz-id="<?php echo $quiz_id; ?>">
        <div class="speaking-parts-container ielts-draggables-container">

            <!-- Instructions Area  -->
            <div class="container-instructions">
                <h2><?php echo __('Builder', ISQ_TXT_DOMAIN); ?></h2>
                <p><?php echo __('Easy To Use Drag Drop Builder For Quiz', ISQ_TXT_DOMAIN); ?></p>
            </div>
            <!-- /Instructions Area  -->

            <!-- Speaking Part Actions  -->
            <div class="draggable-final-actions sp-final-actions">
                <!-- ADD New SP  -->
                <div class="add-new-sp-container final-action">
                    <div class="draggable-ptrigger add-new-sp-trigger">
                        <button>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                            <?php echo __('Create New Speaking Part', ISQ_TXT_DOMAIN); ?>
                        </button>
                    </div>
                    <!-- Should Always be in Last otherwise JS functinality Will Break  -->
                    <div class="add-sp-popup draggable-item-popup">
                        <!-- Close Button Should always be First Child  otherwise JS functinality Will Break-->
                        <span class="close-item-popup">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M242.7 256l100.1-100.1c12.3-12.3 12.3-32.2 0-44.5l-22.2-22.2c-12.3-12.3-32.2-12.3-44.5 0L176 189.3 75.9 89.2c-12.3-12.3-32.2-12.3-44.5 0L9.2 111.5c-12.3 12.3-12.3 32.2 0 44.5L109.3 256 9.2 356.1c-12.3 12.3-12.3 32.2 0 44.5l22.2 22.2c12.3 12.3 32.2 12.3 44.5 0L176 322.7l100.1 100.1c12.3 12.3 32.2 12.3 44.5 0l22.2-22.2c12.3-12.3 12.3-32.2 0-44.5L242.7 256z"/></svg>
                        </span>
                        <div class="draggable-item-popup-inner sp-item-popup-inner">
                            <!-- Speaking Part Editing Options  -->
                            <div class="speaking-part-options">
                                <form id="add-speaking-part" onsubmit="this.event.preventDefault();">
                                    <!-- Title  -->
                                    <div class="isq-field-group no-label">
                                        <label for="sp_title"><?php echo __('Speaking Part Title', ISQ_TXT_DOMAIN); ?></label>
                                        <input type="text" placeholder="<?php echo __('Speaking Part Title Here', ISQ_TXT_DOMAIN); ?>" required name="sp_title" id="sp_title">
                                    </div>

                                    <!-- Description  -->
                                    <div class="isq-field-group">
                                        <label for="sp_desc"><?php echo __('Speaking Part Instructions', ISQ_TXT_DOMAIN); ?></label>
                                        <textarea name="sp_desc" id="sp_desc" cols="30" rows="4"></textarea>
                                    </div>

                                    <!-- Time Field  -->
                                    <div class="isq-field-group">
                                        <label for="allowed_rec_time">Total Recording Time Allowed (Minutes)</label>
                                        <input required type="number" name="allowed_rec_time" id="allowed_rec_time">
                                    </div>

                                    <div class="sp-actions-wrapper">
                                        <input type="hidden" name="sp_id" id="sp_id" value="">
                                        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                                        <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce( 'create_speaking_part' ) ?>">
                                        <input type="hidden" name="author" id="author" value="<?php echo $author->ID ?>">
                                        <button type="button" class="quiz-action" onclick="publishSpeakingPart()"><?php echo __('Add Speaking Part', ISQ_TXT_DOMAIN); ?></button>
                                    </div>

                                </form>
                            </div>
                            <!-- \Speaking Part Editing Options  -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Speaking Part Actions  -->

            <!-- Speaking Parts List -->
            <div class="speaking-parts ielts-draggables">
                <?php echo Helpers::render_builder_contents($quiz_contents); ?>
            </div>
            <!-- /Speaking Parts List  -->
        </div>
    </div>
    <!-- Quiz Builder End  -->
    <?php } ?>
</div>

<?php
// Include Theme Footer
get_footer();
?>


