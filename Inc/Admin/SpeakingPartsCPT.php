<?php
namespace ISQNS\Admin;
use \ISQNS\Base\CPTBase;
use \ISQNS\Admin\SpeakingQuizCPT as Quiz;
class SpeakingPartsCPT {

    private $post_type_name = "Speaking Part";

    private static $post_type_id;

    public function register(){

        // Register Post Type
        new CPTBase($this->post_type_name, array('show_in_menu'=>false));

        // Generate ID - For Standardizing Ids are generated using post_type_name
        self::$post_type_id = CPTBase::uglify($this->post_type_name);

        // Add to Submenu
        // add_action('admin_menu', array($this, 'add_to_menu'));

        // Add Custom Text Above Editor
        // add_action( 'edit_form_after_title', array($this,'text_before_editor'));
        add_action( 'edit_form_after_title', array($this,'render_sp_meta'));
    }

    public function add_to_menu(){
        add_submenu_page(
             'edit.php?post_type='.SpeakingQuizCPT::get_post_type_id(),
             'Speaking Parts',
             'Speaking Parts',
             'manage_options',
             'edit.php?post_type='.self::$post_type_id,
             );
    }

    public function text_before_editor($post){
        if($post->post_type == self::$post_type_id){
            echo '<h2 style="padding:0">Instructions for Speaking Part</h2>';
            echo '<p style="margin:0">These Insturctions Will be displayed before beginning the speaking parts</p>';
        }
    }

    public static function get_post_type_id(){
        return self::$post_type_id;
    }

    public function render_sp_meta($post){ ?>
        <?php if($post->post_status != 'auto-draft' && $post->post_type == self::get_post_type_id()){ 
        $sp_data = get_post_meta( $post->ID, 'added_locations', true );    
        ?>
        <div class="wp-ielts-container">
            <div class="wp-ielts-container-head">
                Information
            </div>
            <div class="wp-ielts-container-body">
                <?php 
                $quiz = get_post($sp_data['quiz_id']);
                if(! $quiz){
                    echo 'Quiz Linked with this Speaking Part Seems to Be Deleted';
                    echo 'This Speaking is no longer functional and can be deleted';
                    return;
                }
                ?>
                <div>
                    <p>This speaking part belongs to Following Quiz:</p>
                    <strong><?php echo $quiz->post_title; ?></strong>
                </div>
                <a href="<?php echo Quiz::get_edit_url($quiz->ID); ?>" target="_blank" class="button button-primary">Edit Quiz</a>
            </div>
        </div>
        <?php } ?>
    <?php }
}