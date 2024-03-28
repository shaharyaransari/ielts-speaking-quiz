<?php
use \ISQNS\Base\BaseHelpers;

BaseHelpers::validate_ielts_user();

wp_head(  ); ?>

<?php 


$ld_quiz_id = isset( $_REQUEST['ld-quiz'] ) ? $_REQUEST['ld-quiz'] : 0;
$ld_course = isset($_REQUEST['course']) ? $_REQUEST['course'] : 0;
$ld_quiz_link = '';
$quiz_id = get_the_ID(  );
$quiz_title = get_the_title();
$quiz_desc = apply_filters('the_content', get_post_field('post_content', $quiz_id)); 
$quiz_elements = get_post_meta( $quiz_id , 'quiz_conents_json', true);
if($quiz_elements){
$quiz_elements = json_decode(stripslashes($quiz_elements),true);
$quiz_elements = $quiz_elements['contents'];
}else{
    $quiz_elements = array();
}
?>
<div class="single-ielts-quiz-page">
    <div class="ielts-speaking-quiz-wrap">
        <!-- Quiz sticky Header  -->
        <div class="isq-header">
            <div id="timeCounter">00:00:00</div>
            <input type="hidden" id="ld_quiz" value="<?php echo $ld_quiz_id; ?>">
            <input type="hidden" id="ld_course" value="<?php echo $ld_course; ?>">
        </div>
        <!-- /Quiz sticky  Header  -->


        <!-- Quiz Body Wrap  -->
            <div class="isq-body">
                <!-- Content Wrap  -->
                    <div class="isq-quiz-content-wrap" id="isq-quiz-content-wrap">
                        <!-- Quiz Content  -->
                            <div class="isq-quiz-content">
                                <!-- Quiz Content Header  -->
                                    <div class="isq-quiz-content-header" id="isq-quiz-content-header">
                                    <?php echo $quiz_title; ?>
                                    </div>
                                <!-- /Quiz Content Header  -->

                                <!-- Quiz Content Body  -->
                                <div class="isq-quiz-content-body" id="isq-quiz-content-body">
                                    <div class="isq-quiz-content-body-inner">
                                        <?php echo $quiz_desc; ?>
                                    </div>
                                    <div class="quiz-content-body-actions">
                                        <!-- <button class="quiz-body-action">Start Quiz</button> -->
                                    </div>
                                </div>
                                <!-- /Quiz Content Body  -->

                                <!-- Quiz Content Footer  -->
                                <div class="isq-quiz-content-footer" id="isq-quiz-content-footer">
                                    <button class="quiz-content-footer-action" onclick="startSpeakingQuiz()">Start Quiz</button>
                                </div>
                                <!-- /Quiz Content Footer  -->
                            </div>
                        <!-- /Quiz Content  -->
                    </div>
                <!-- /Content Wrap  -->
            </div>
        <!-- Quiz Body Wrap  -->


        <!-- Quiz sticky Footer  -->
        <div class="isq-footer" id="isq-footer">
            Please Start Quiz to See Navigation Options
        </div>
        <!-- Quiz sticky Footer  -->

        <!-- Quiz Speaking Part Template  -->
        <template id="speaking-part-template">
            <div class="isq-quiz-content">
                <!-- Quiz Content Header  -->
                    <div class="isq-quiz-content-header" id="isq-quiz-content-header">
                        <div class="skeleton skeleton-text"></div>                         
                    </div>
                <!-- /Quiz Content Header  -->

                <!-- Quiz Content Body  -->
                <div class="isq-quiz-content-body" id="isq-quiz-content-body">
                    <div class="isq-quiz-content-body-inner">
                        <div class="sp-number">
                            <div class="skeleton skeleton-text"></div>
                        </div>
                        <div class="sp-content">
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
                <!-- /Quiz Content Body  -->

                <!-- Quiz Content Footer  -->
                <div class="isq-quiz-content-footer" id="isq-quiz-content-footer">
                    <button class="quiz-content-footer-action" onclick="showFirstSpEl()">Next</button>
                </div>
                <!-- /Quiz Content Footer  -->
            </div>
        </template>
        <!-- \Quiz Speaking Part Template  -->


        <!-- Quiz Question Template  -->
        <template id="ielts-question-template">
            <div class="isq-quiz-content">
                <!-- Quiz Content Header  -->
                    <div class="isq-quiz-content-header" id="isq-quiz-content-header">
                        Speaking Part Title                                
                    </div>
                <!-- /Quiz Content Header  -->

                <!-- Quiz Content Body  -->
                <div class="isq-quiz-content-body" id="isq-quiz-content-body">
                    <div class="isq-quiz-content-body-inner">
                        <div class="question-number-wrap">
                            <div class="player-wrap"></div>
                            <div class="question-number">
                                Question 1
                            </div>
                        </div>
                        
                        <div class="question-content-wrap"> 
                            <div class="question-title">
                                Where Are You From?
                            </div>
                            <div class="question-content">
                                Question Content Here
                            </div>
                        </div>
                        <!-- Recorder  -->
                        <div class="question-recorder-module-wrap"></div>
                        <!-- \Recorder  -->

                        <!-- Response  -->
                        <div class="question-response-wrap" id="question-response-wrap">
                            <div class="question-response">
                                <div class="quesiton-response-title">
                                    Your Response:
                                </div>
                                <div class="question-response-content">
                                    Please Record Audio
                                </div>
                            </div>
                        </div>
                        <!-- /Response  -->

                        <!-- Improved Answer  -->
                        <div class="improved-answer-wrap q-dynamic-section" id="improved-answer-wrap">
                            <div class="improved-answer">
                                <div class="improved-answer-title">
                                    Improved Answer
                                </div>
                                <div class="improved-answer-content">
                                    Thinking...
                                </div>
                            </div>
                        </div>
                        <!-- /Response  -->
                    </div>
                    <div class="quiz-content-body-actions">
                        <button class="quiz-body-action action-disabled" id="improve-response-trigger" onclick="getImprovedAnswer()">Improve</button>
                    </div>
                </div>
                <!-- /Quiz Content Body  -->

                <!-- Quiz Content Footer  -->
                <div class="isq-quiz-content-footer" id="isq-quiz-content-footer">
                    <button class="quiz-content-footer-action" id="retry-btn" onclick="resetQuestionData()">Retry</button>
                    <button class="quiz-content-footer-action action-secondary" id="next-quiz-el-btn" onclick="showNextQuizEl()">Next Question</button>
                    <button class="quiz-content-footer-action action-secondary action-hidden" id="submit-quiz-btn-inner" onclick="submitQuiz()" data-nonce="<?php echo wp_create_nonce('submit_ielts_quiz'); ?>">Submit Quiz</button>
                </div>
                <!-- /Quiz Content Footer  -->
            </div>
        </template>
        <!-- \Quiz Question Template  -->
    </div>
    <div class="notification-box"></div>
</div>
<?php
wp_footer(  );
?>