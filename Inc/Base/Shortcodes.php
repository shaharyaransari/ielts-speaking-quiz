<?php
namespace ISQNS\Base;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Admin\SpeakingPartsCPT as Part;
use \ISQNS\Admin\SpeakingQuizQuestionsCPT as Question;
use \ISQNS\Base\TemplateLoader;
use \ISQNS\Result\ResultsManager;
class Shortcodes{
    public function register(){
        // For Rendering Quiz Trigger
        add_shortcode( 'ielts-quiz-trigger', array($this, 'render_ielts_quiz_trigger') );

        // For Rendering User Results
        add_shortcode( 'ielts-quiz-results', array($this, 'render_ielts_quiz_results') );

        // For Displaying Quiz List
        add_shortcode( 'speaking-quiz-list', array($this, 'render_ielts_speaking_quiz_list') );

        // Debug info
        add_shortcode( 'debug-info', array($this, 'debug_info') );
    }

    public function debug_info(){
        ob_start();
        echo '<pre>';
        echo var_dump(get_post_meta('2506'));
        echo '<pre>';
        return ob_get_clean();
    }
    public function render_ielts_quiz_trigger($atts){
        $args = shortcode_atts( array(
            'id' => null,
        ), $atts );

        $quiz_id = $args['id'];
        ob_start();
        if($quiz_id){
            $quiz_url = get_the_permalink($quiz_id); 
            $quiz_title = get_the_title($quiz_id);
            $quiz_desc = apply_filters('the_content', get_post_field('post_content', $quiz_id));
            if($quiz_url){
            ?>
                <div class="ielts-quiz-trigger-wrap">
                    <h1 class="quiz-trigger-title"><?php echo $quiz_title; ?></h1>
                    <div class="ielts-quiz-desc-wrap">
                        <h2><?php echo __('Instructions', ISQ_TXT_DOMAIN); ?></h2>
                        <div class="ielts-quiz-desc-wrap-inner"><?php echo $quiz_desc; ?></div>
                        <a href="<?php echo $quiz_url; ?>" class="quiz-trigger quiz-action"><?php echo __('Start Quiz', ISQ_TXT_DOMAIN); ?></a>
                    </div>
                </div>
                
            <?php
            }else{
                echo __('Quiz Does not exists', ISQ_TXT_DOMAIN);
            }
        }else{
            echo __('Quiz id Required', ISQ_TXT_DOMAIN);
        }
        return ob_get_clean();
    }

    public static function user_has_role($role_id) {
        $current_user = wp_get_current_user();
        if (!empty($current_user->roles) && in_array($role_id, $current_user->roles)) {
            return true;
        } else {
            return false;
        }
    }

    public function render_ielts_quiz_results($atts){
        $current_user = wp_get_current_user();
        $args = shortcode_atts( array(
            'user_id' => null,
            'instructor' => null,
            'admin' => null
        ), $atts);
        $user_id = $args['user_id'];
        $for_instructor = false;
        $for_admin = false;

        if($args['admin'] != null || current_user_can('manage_options')){
            $for_admin = true;
        }elseif($args['instructor'] != null || self::user_has_role('wdm_instructor')){
            $for_instructor = true;
        }

        if($for_admin){
            $results = ResultsManager::get_all_results();
        }elseif($for_instructor){
            $author_id = wp_get_current_user(  );
            $author_id = $author_id->ID;
            $results = ResultsManager::get_results_by_author($author_id);
        }else{

            if($args['user_id'] === null){
                $current_user = wp_get_current_user(  );
                $user_id = intVal($current_user->ID);
            }
            $results = ResultsManager::get_results_by_user($user_id);
        }
        ob_start();
        echo "<div class='result-list'>";
        foreach($results as $result_raw){
            $result_user_id = $result_raw['user_id'];
            $result = $result_raw['result_obj'];
            $quiz_id = $result['quiz_id'];
            $try_number = $result['try_number'];
            $quiz_title = get_the_title( $quiz_id );
            $start_date = $result['time'];
            // if( !$start_date ){
            //     continue; // for Invalid Results
            // }
            // echo '<pre>';
            // echo var_dump($result);
            // echo '</pre>';
            $timestampInSeconds = $start_date / 1000;
            $parts_count = is_array($result['result_elements']) ? count($result['result_elements']) :  0;
            $date = new \DateTime("@$timestampInSeconds");
            $formattedDate = $date->format('F j, Y H:i:s');
            $result_url = ResultsManager::get_quiz_result_page_url($quiz_id,$try_number,$result_user_id);
            
            ?>
            <div class="single-result-wrap">
                <!-- Title Wrap  -->
                <div class="r-quiz-title-wrap">
                    <div class="r-quiz-title">
                        <?php echo $quiz_title; ?> <span class="r-date-taken">Date: <?php echo $formattedDate; ?></span>
                    </div>
                    <div class="r-quiz-meta">
                        <span>Speaking Parts: <?php echo $parts_count; ?></span>
                    </div>
                </div>
                <!-- /Title Wrap  -->

                <!-- Actions  -->
                <div class="quiz-actions">
                    <a href="<?php echo $result_url; ?>" class="quiz-action">Show Result</a>
                </div>
                <!-- /Actions  -->

            </div>
        <?php }
        echo "</div>";
        return ob_get_clean();
    }


    public function render_ielts_speaking_quiz_list(){
        ob_start();
        $TemplateLoader =  new TemplateLoader();
        $TemplateLoader->get_template_part('speaking-quiz-list-shortcode' );
        return ob_get_clean();
    }
}
