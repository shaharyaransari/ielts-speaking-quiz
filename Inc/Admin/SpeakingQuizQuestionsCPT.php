<?php
namespace ISQNS\Admin;
use \ISQNS\Base\CPTBase;
class SpeakingQuizQuestionsCPT {

    private $post_type_name = "S Quiz Question"; // For Class
    private static $post_type_id;

    private $singular_name = 'Quiz Question';

    private $plural_name = "Quiz Questions";

    public function register(){

        // Register Post Type
        $cpt = new CPTBase(
            $this->post_type_name,
            array('show_in_menu'=>false),
            array(
                'name' 					=> _x( $this->plural_name, 'post type general name' ),
                'singular_name' 		=> _x( $this->singular_name, 'post type singular name' ),
                'add_new' 				=> _x( 'Add New', strtolower( $this->singular_name ) ),
                'add_new_item' 			=> __( 'Add New ' . $this->singular_name ),
                'edit_item' 			=> __( 'Edit ' . $this->singular_name ),
                'new_item' 				=> __( 'New ' . $this->singular_name ),
                'all_items' 			=> __( 'All ' . $this->plural_name ),
                'view_item' 			=> __( 'View ' . $this->singular_name ),
                'search_items' 			=> __( 'Search ' . $this->plural_name ),
                'not_found' 			=> __( 'No ' . strtolower( $this->plural_name ) . ' found'),
                'not_found_in_trash' 	=> __( 'No ' . strtolower( $this->plural_name ) . ' found in Trash'), 
                'parent_item_colon' 	=> '',
                'menu_name' 			=> $this->plural_name
            ), 
        );

        // Generate ID - For Standardizing Ids are generated using post_type_name
        self::$post_type_id = CPTBase::uglify($this->post_type_name);

        // Add Custom Text Above Editor
        add_action( 'edit_form_after_title', array($this,'render_sp_meta'));
        }

    public static function get_post_type_id(){
        return self::$post_type_id;
    }

    public function render_sp_meta($post){ ?>
        <?php if($post->post_status != 'auto-draft' && $post->post_type == self::get_post_type_id()){ 
        $iq_data = get_post_meta( $post->ID, 'question_audio')[0];    
        ?>
        <div class="wp-ielts-container">
            <div class="wp-ielts-container-head">
                Question Audio Information (Can only be edited in Quiz builder)
            </div>
            <div class="wp-ielts-container-body">
                <?php 
                $audio_url = $iq_data['audio_url'];
                $audio_input_type = $iq_data['question_audio_upload_type'];
                $audio_input_label = '';
                if($audio_input_type == 'self_record_audio'){
                    $audio_input_label = 'Self Recorded';
                }elseif($audio_input_type == 'upload_audio_file'){
                    $audio_input_label = 'File Upload';
                }elseif($audio_input_type == 'file_url'){
                    $audio_input_label = 'Audio File URL Given';
                }
                ?>
                <div>
                    <p>Audio Input Type: <strong><?php echo $audio_input_label; ?></strong></p>
                    <div class="audio-preview">
                        <audio style="width:100%" src="<?php echo $audio_url; ?>" preload="true" controls></audio>
                    </div>
                </div>
                
            </div>
        </div>
        <?php } ?>
    <?php }
}