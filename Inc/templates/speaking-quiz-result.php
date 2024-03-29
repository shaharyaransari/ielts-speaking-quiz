<?php
use \ISQNS\Result\ResultsManager;
// get_header(); 
wp_head(  );
if(isset($_REQUEST['quiz']) && isset($_REQUEST['try'])){
    $quiz = intVal($_REQUEST['quiz']);
    $try = intVal($_REQUEST['try']);
    $user_id = intVal($_REQUEST['uid']);
    // $meta_key = "ielts_quiz_{$quiz}_{$try}";
    // $result_obj = get_user_meta( $user_id , $meta_key, true );
    $result_obj = ResultsManager::get_result($try, $user_id, $quiz);
    // echo var_dump($result_obj);
    if(! $result_obj){
        die ('Result Does Not Exists');
    }
    $ld_quiz_id = $result_obj['ld_quiz'];
    $ld_course = $result_obj['ld_course'];
    $ld_quiz_link = null;
    if(function_exists('learndash_get_step_permalink') && $ld_course && $ld_quiz_id){
        $ld_quiz_link = learndash_get_step_permalink($ld_quiz_id, $ld_course) . '?completed';
    }
}

?>
<div class="quiz-result-page">
    <div class="results-loading">
        <!-- Loading  -->
        <div class="skeleton-header"></div>
        <div class="quiz-result-wrapper">
            <div class="quiz-result-wrapper-inner">
                <!-- Left Area  -->
                <div class="result-left-wrapper">
                    <div class="result-left-wrapper-inner">
                        <div class="left-wrapper-header">
                            <div class="speaking-part-title">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- Questions List  -->
                        <div class="result-questions-list-wrap">
                            <div class="result-questions-list">
                                <div class="result-question">
                                    <div class="question-title">
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                    </div>
                                    <!-- Response Wrap  -->
                                    <div class="question-response-wrap">
                                        <!-- Player  -->
                                        <div class="player">
                                            <!-- New  -->
                                            <div class="recording-preview-wrap">
                                                <div class="recording-preview">
                                                    <div class="question-audio-wrap">
                                                        <audio src="" controls="" controlslist="nodownload"></audio>
                                                        <div class="question-audio-trigger" data-playing="false">
                                                            <span class="play-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"></path></svg>
                                                            </span>
                                                            <span class="recording-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"></path></svg>
                                                            </span>
                                                            <span class="pause-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"></path></svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="recording-log"><div class="skeleton skeleton-text"></div></div>
                                            </div>
                                            <!-- /New  -->
                                        </div>
                                        <!-- /Player  -->

                                        <!-- Transcript  -->
                                        <div class="question-response">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                        <!-- Transcript  -->
                                    </div>
                                    <!-- /Response Wrap  -->
                                </div>
                            
                                <div class="result-question">
                                    <div class="question-title">
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                    </div>
                                    <!-- Response Wrap  -->
                                    <div class="question-response-wrap">

                                        <!-- Player  -->
                                        <div class="player">
                                            <!-- New  -->
                                            <div class="recording-preview-wrap">
                                                <div class="recording-preview">
                                                    <div class="question-audio-wrap">
                                                        <audio src="" controls="" controlslist="nodownload"></audio>
                                                        <div class="question-audio-trigger" data-playing="false">
                                                            <span class="play-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"></path></svg>
                                                            </span>
                                                            <span class="recording-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"></path></svg>
                                                            </span>
                                                            <span class="pause-icon audio-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"></path></svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="recording-log">
                                                    <div class="skeleton skeleton-text"></div>
                                                </div>
                                            </div>
                                            <!-- /New  -->
                                        </div>
                                        <!-- /Player  -->

                                        <!-- Transcript  -->
                                        <div class="question-response">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                        <!-- Transcript  -->
                                    </div>
                                    <!-- /Response Wrap  -->
                                </div>
                            </div>
                        </div>
                        <!-- /Questions List  -->
                    </div>
                </div>
                <!-- Left Area  -->

                <!-- Right Area  -->
                <div class="result-right-wrapper">
                    <div class="result-left-wrapper-inner">
                        <!-- Score Boxes  -->
                        <div class="score-boxes">
                            <!-- Score Box  -->
                            <div class="score-box skeleton">
                                <div class="score-box-title">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"></path></svg>
                                    </span>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <div class="score-box-score"><div class="skeleton skeleton-text"></div></div>
                            </div>
                            <!-- /Score Box  -->
                            <!-- Score Box  -->
                            <div class="score-box skeleton">
                                <div class="score-box-title">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"></path></svg>
                                    </span>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <div class="score-box-score"><div class="skeleton skeleton-text"></div></div>
                            </div>
                            <!-- /Score Box  -->
                            <!-- Score Box  -->
                            <div class="score-box skeleton">
                                <div class="score-box-title">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"></path></svg>
                                    </span>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <div class="score-box-score"><div class="skeleton skeleton-text"></div></div>
                            </div>
                            <!-- /Score Box  -->
                            <!-- Score Box  -->
                            <div class="score-box skeleton">
                                <div class="score-box-title">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"></path></svg>
                                    </span>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <div class="score-box-score"><div class="skeleton skeleton-text"></div></div>
                            </div>
                            <!-- /Score Box  -->
                        </div>
                        <!-- /Score Boxes  -->

                        <!-- Suggegstions Wrapper  -->
                        <div class="result-suggestions-wrapper">
                            <div class="result-suggestions-desc">
                                Suggestions: 
                            </div>
                            <div class="result-suggestions-wrap-inner">
                                <!-- Single Suggestion  -->
                                <div class="result-suggestion">
                                    <div class="suggestion-header skeleton-loading">
                                        <span class="orignal-txt">
                                            <div class="skeleton skeleton-text"></div>
                                        </span>
                                        <span class="saperator">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>
                                        </span>
                                        <span class="suggestion-txt">
                                            Loading...
                                        </span>
                                    </div>
                                    <div class="suggestion-body">
                                        <div class="suggestion-exp">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Single Suggestion  -->
                            
                                
                                <!-- Single Suggestion  -->
                                <div class="result-suggestion">
                                    <div class="suggestion-header skeleton-loading">
                                        <span class="orignal-txt">
                                            <div class="skeleton skeleton-text"></div>
                                        </span>
                                        <span class="saperator">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>
                                        </span>
                                        <span class="suggestion-txt">
                                            Loading...
                                        </span>
                                    </div>
                                    <div class="suggestion-body">
                                        <div class="suggestion-exp">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Single Suggestion  -->
                            </div>
                        </div>
                        <!-- /Suggegstions Wrapper  -->
                    </div>
                </div>
                <!-- Right Area  -->
            </div>
        </div>
    </div>
    <!-- /Loading  -->
    <div class="quiz-result-header">
        <?php if($ld_quiz_link){ ?>
            <a href="<?php echo $ld_quiz_link; ?>" class="quiz-action">Back to Course</a>
        <?php } ?>
    </div>
    <!-- Grammer Vocab Result  -->
    <div class="quiz-result-wrapper">
        <div class="quiz-result-wrapper-inner">
            <!-- Left Area  -->
            <div class="result-left-wrapper">
                <div class="result-left-wrapper-inner">
                    <div class="left-wrapper-header">
                        <div class="speaking-part-title" id="speaking-part-title">
                            Speaking Part Title Here
                        </div>
                        <div class="fluency-legend-wrap" style="opacity:0">
                            <div class="fluency-legend">

                                <div class="missed-pause-legend-wrap">
                                    <div class="miss-pause-legend">
                                        <span class="missed-pause-symbol"></span>
                                    </div>
                                    <div class="miss-pause-label">
                                        Missed Pauses
                                    </div>
                                </div>

                                <div class="bad-pause-legend-wrap">
                                    <div class="bad-pause-legend">
                                        <span class="bad-pause-symbol">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </span>
                                    </div>
                                    <div class="bad-pause-label">
                                        Bad Pauses
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Questions List  -->
                    <div class="result-questions-list-wrap" id="result-questions-list-wrap">
                        <div class="result-questions-list">
                            <div class="result-question">
                                <div class="question-title">
                                    <div class="skeleton skeleton-text"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <!-- Response Wrap  -->
                                <div class="question-response-wrap">
                                    <!-- Player  -->
                                    <div class="player">
                                        <!-- New  -->
                                        <div class="recording-preview-wrap">
                                            <div class="recording-preview">
                                                <div class="question-audio-wrap">
                                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                                    <div class="question-audio-trigger" data-playing="false">
                                                        <span class="play-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"></path></svg>
                                                        </span>
                                                        <span class="recording-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"></path></svg>
                                                        </span>
                                                        <span class="pause-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"></path></svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recording-log"><div class="skeleton skeleton-text"></div></div>
                                        </div>
                                        <!-- /New  -->
                                    </div>
                                    <!-- /Player  -->

                                    <!-- Transcript  -->
                                    <div class="question-response">
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                    </div>
                                    <!-- Transcript  -->
                                </div>
                                <!-- /Response Wrap  -->
                            </div>
                        
                            <div class="result-question">
                                <div class="question-title">
                                    <div class="skeleton skeleton-text"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                                <!-- Response Wrap  -->
                                <div class="question-response-wrap">

                                    <!-- Player  -->
                                    <div class="player">
                                        <!-- New  -->
                                        <div class="recording-preview-wrap">
                                            <div class="recording-preview">
                                                <div class="question-audio-wrap">
                                                    <audio src="" controls="" controlslist="nodownload"></audio>
                                                    <div class="question-audio-trigger" data-playing="false">
                                                        <span class="play-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c7.6-4.2 16.8-4.1 24.3 .5l144 88c7.1 4.4 11.5 12.1 11.5 20.5s-4.4 16.1-11.5 20.5l-144 88c-7.4 4.5-16.7 4.7-24.3 .5s-12.3-12.2-12.3-20.9V168c0-8.7 4.7-16.7 12.3-20.9z"></path></svg>
                                                        </span>
                                                        <span class="recording-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256-96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"></path></svg>
                                                        </span>
                                                        <span class="pause-icon audio-icon">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm224-72V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24zm112 0V328c0 13.3-10.7 24-24 24s-24-10.7-24-24V184c0-13.3 10.7-24 24-24s24 10.7 24 24z"></path></svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recording-log">
                                                <div class="skeleton skeleton-text"></div>
                                            </div>
                                        </div>
                                        <!-- /New  -->
                                    </div>
                                    <!-- /Player  -->

                                    <!-- Transcript  -->
                                    <div class="question-response">
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                        <div class="skeleton skeleton-text"></div>
                                    </div>
                                    <!-- Transcript  -->
                                </div>
                                <!-- /Response Wrap  -->
                            </div>
                        </div>
                    </div>
                    <!-- /Questions List  -->
                </div>
            </div>
            <!-- Left Area  -->

            <!-- Right Area  -->
            <div class="result-right-wrapper">
                <div class="result-left-wrapper-inner">

                    <!-- Score Boxes  -->
                    <div class="score-boxes">
                        <!-- Score Box  -->
                        <div class="score-box vocabulary active" onclick="loadSpResultParts('vocabulary')">
                            <div class="score-box-title">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                </span>
                                Vocabulary
                            </div>
                            <div class="score-box-score">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /Score Box  -->
                        <!-- Score Box  -->
                        <div class="score-box grammer" onclick="loadSpResultParts('grammer')">
                            <div class="score-box-title">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                </span>
                                Grammar
                            </div>
                            <div class="score-box-score">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /Score Box  -->
                        <!-- Score Box  -->
                        <div class="score-box pronunciation" onclick="loadSpResultParts('pronunciation')">
                            <div class="score-box-title">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                </span>
                                Pronunciation
                            </div>
                            <div class="score-box-score">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /Score Box  -->
                        <!-- Score Box  -->
                        <div class="score-box fluency" onclick="loadSpResultParts('fluency')">
                            <div class="score-box-title">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                                </span>
                                Fluency
                            </div>
                            <div class="score-box-score">
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                        <!-- /Score Box  -->
                    </div>
                    <!-- /Score Boxes  -->

                    <!-- Suggegstions Wrapper  -->
                    <div class="result-suggestions-wrapper" id="result-suggestions-wrapper">
                            <div class="result-suggestions-desc">
                                Suggestions: 
                            </div>
                            <div class="result-suggestions-wrap-inner">
                                <!-- Single Suggestion  -->
                                <div class="result-suggestion">
                                    <div class="suggestion-header skeleton-loading">
                                        <span class="orignal-txt">
                                            <div class="skeleton skeleton-text"></div>
                                        </span>
                                        <span class="saperator">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>
                                        </span>
                                        <span class="suggestion-txt">
                                            Loading...
                                        </span>
                                    </div>
                                    <div class="suggestion-body">
                                        <div class="suggestion-exp">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Single Suggestion  -->
                            
                                
                                <!-- Single Suggestion  -->
                                <div class="result-suggestion">
                                    <div class="suggestion-header skeleton-loading">
                                        <span class="orignal-txt">
                                            <div class="skeleton skeleton-text"></div>
                                        </span>
                                        <span class="saperator">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>
                                        </span>
                                        <span class="suggestion-txt">
                                            Loading...
                                        </span>
                                    </div>
                                    <div class="suggestion-body">
                                        <div class="suggestion-exp">
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Single Suggestion  -->
                            </div>
                    </div>
                    <!-- /Suggegstions Wrapper  -->

                </div>
            </div>
            <!-- Right Area  -->
        </div>
    </div>
    <!-- Grammer Vocab Result  -->
    <div class="quiz-result-footer" id="quiz-result-footer">
        Result Footer Will Contain Navigation
    </div>

    <!-- Grammer Vocab Question Template  -->
    <template id="grammer-vocabulary-question">
        <div class="result-question">
            <div class="question-title">
                Question 1:  What is Your Favorite Food
            </div>
            <!-- Response Wrap  -->
            <div class="question-response-wrap">

                <!-- Player  -->
                <div class="player">Player</div>
                <!-- /Player  -->

                <!-- Transcript  -->
                <div class="question-response">
                    Question Transcript and Response Here
                </div>
                <!-- Transcript  -->

            </div>
            <!-- /Response Wrap  -->
        </div>
    </template>
    <!-- Grammer Vocab Question Template  -->
    
    <!-- Suggestions Wrap For Grammer  -->
    <template id="grammer-suggestions">
            <div class="result-suggestions-desc">
                Suggestions: 
            </div>
            <div class="result-suggestions-wrap-inner"></div>
    </template>
    <!-- /Suggestions Wrap For Grammer  -->

    <!-- Suggestions Wrap For Vocab  -->
    <template id="vocabulary-suggestions">
            <div class="result-suggestions-desc">
                Suggestions: 
            </div>
            <div class="result-suggestions-wrap-inner"></div>
    </template>
    <!-- /Suggestions Wrap For Vocab  -->

    <!-- Grammer Suggestion Template  -->
    <template id="grammer-suggestion-temp">
            <!-- Single Suggestion  -->
            <div class="result-suggestion">
                <div class="suggestion-header">
                    <span class="orignal-txt"></span>
                    <span class="saperator">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>
                    </span>
                    <span class="suggestion-txt"></span>
                </div>
                <div class="suggestion-body">
                    <div class="suggestion-exp"></div>
                </div>
            </div>
            <!-- /Single Suggestion  -->
    </template>
    <!-- Grammer Suggestion Template  -->

    <!-- Vocabulary Suggestion Template  -->
    <template id="vocabulary-suggestion-temp">
        <!-- Single Suggestion  -->
        <div class="result-suggestion">
            <div class="suggestion-header">
                <span class="orignal-txt"></span>
                <span class="saperator">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>
                </span>
                <span class="suggestion-txt"></span>
            </div>
            <div class="suggestion-body">
                <div class="suggestion-exp"></div>
            </div>
        </div>
        <!-- /Single Suggestion  -->
    </template>
    <!-- Vocabulary Suggestion Template  -->

    <!-- Question List Template Pronunciation -->
    <template id="pronunciation-question">
        <div class="result-question">
            <div class="question-title">
                Question 1:  What is Your Favorite Food
            </div>
            <!-- Response Wrap  -->
            <div class="question-response-wrap">

                <!-- Player  -->
                <div class="player">Player</div>
                <!-- /Player  -->

                <!-- Transcript  -->
                <div class="question-response">
                    Question Transcript and Response Here
                </div>
                <!-- Transcript  -->

            </div>
            <!-- /Response Wrap  -->
        </div>
    </template>
    <!-- /Question List Template Pronunciation -->

    <!-- Suggestions Wrap For Pronunciation  -->
    <template id="pronunciation-suggestions">
            <div class="result-suggestions-desc">
                Errors and Corrections: 
            </div>
            <div class="result-suggestions-wrap-inner"></div>
    </template>
    <!-- /Suggestions Wrap For Pronunciation  -->

    <!-- Pronunciation Suggestion Template  -->
    <template id="pronunciation-suggestion-temp">
        <!-- Single Suggestion  -->
        <div class="result-suggestion pronun-error-wrap">
            <div class="suggestion-header">
                <span class="orignal-txt"></span>
                <span class="saperator">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>
                </span>
                <span class="suggestion-txt"></span>
            </div>
            <div class="suggestion-body">
                <div class="suggestion-exp"></div>
            </div>
        </div>
        <!-- /Single Suggestion  -->
    </template>
    <!-- /Pronunciation Suggestion Template  -->

    <!-- Question List Template Fluency -->
    <template id="fluency-question">
        <div class="result-question">
            <div class="question-title">
                Question 1:  What is Your Favorite Food
            </div>
            <!-- Response Wrap  -->
            <div class="question-response-wrap">

                <!-- Player  -->
                <div class="player">Player</div>
                <!-- /Player  -->

                <!-- Transcript  -->
                <div class="question-response">
                    Question Transcript and Response Here
                </div>
                <!-- Transcript  -->

            </div>
            <!-- /Response Wrap  -->
        </div>
    </template>
    <!-- /Question List Template Fluency -->

    <!-- Suggestions Wrap For Fluency  -->
    <template id="fluency-suggestions">
            <div class="result-suggestions-desc">
            </div>
            <div class="result-suggestions-wrap-inner"></div>
    </template>
    <!-- /Suggestions Wrap For Fluency  -->

    <!-- Fluency Suggestion Template  -->
    <template id="fluency-suggestion-temp">
        <!-- Single Suggestion  -->
        <div class="result-suggestion">
            <div class="suggestion-header">
                <div class="orignal-txt"></div>
                <div class="suggestion-txt"></div>
            </div>
            <div class="suggestion-body">
                <div class="suggestion-exp"></div>
            </div>
        </div>
        <!-- /Single Suggestion  -->
    </template>
    <!-- Fluency Suggestion Template  -->

    <!-- Player Template  -->
    <template id="audio-player">
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
                00:00:00
            </div>
        </div>
        <!-- /New  -->
    </template>
    <!-- /Player Template  -->
    <div class="notification-box"></div>
</div>
<?php 
// get_footer();
wp_footer(  );