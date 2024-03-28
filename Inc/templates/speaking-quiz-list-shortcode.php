<?php 
use \ISQNS\QuizBuilder\Helpers;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
use \ISQNS\Base\TemplateLoader;
$TempLoader =  new TemplateLoader();
$author = wp_get_current_user();
$author_id = $author->ID;
if(current_user_can('manage_options')){
   $author_id = null; 
}
echo var_dump($author_id);
$args = array(
    'post_type' => Quiz::get_post_type_id(),
    'posts_per_page' => 10,
    'author' => $author_id,
    'paged' => 1,
    'post_status' => array('draft', 'publish' ),
);
$query = new WP_Query($args);
?>
<div class="speaking-quiz-archive-page">
<div class="notification-box"></div>
    <div class="quizzes-list-header">
        <div class="quiz-header-actions">
            <button class="quiz-header-action quiz-action" onclick="toggleCheckboxes()">Select All</button>
            <a class="quiz-header-action quiz-action" href="<?php echo Quiz::get_add_new_url(); ?>" target="_blank">Add New Quiz</a>
            <button class="quiz-header-action quiz-action" data-disabled="true" onclick="deleteQuizzes()" data-nonce="<?php echo wp_create_nonce('delete_ielts_quizzes'); ?>">Delete Selected</button>
        </div>
    </div>
    <?php if($query->have_posts(  )){ ?>
    <div class="ielts-speaking-quizzes-list" id="ielts-speaking-quizzes-list">
        <?php 
        while ($query->have_posts(  )){
            $query->the_post();
            echo var_dump(get_post_field( 'post_author', get_the_ID() ));
            $TempLoader->get_template_part('content', 'speaking-quiz' );
        }
        ?>
    </div>
    <?php }else{
        echo 'No Quiz Found Please Create One';
    } ?>
    <div class="quizzez-list-footer">
        <div id="quiz_pagination">
            <?php echo paginate_links(array('total'=>$query->max_num_pages)); ?>
        </div>
    </div>
</div>
<?php