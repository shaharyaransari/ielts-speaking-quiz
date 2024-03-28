<?php
namespace ISQNS\Quiz;

class Helpers {
    /**
     * $file should be an array element of $_FILES
     */
    public static function uploadFile($file){
        $success = true;
        $error = null;
        $attachment_id = null;
        $upload = null;

        $upload = wp_handle_upload( 
            $file,
            array( 'test_form' => false ) 
        );

        if( ! empty( $upload[ 'error' ] ) ){
            $success = false;
            $error = $upload['error'];
        }

        // it is time to add our uploaded image into WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $upload[ 'url' ],
                'post_mime_type' => $upload[ 'type' ],
                'post_title'     => basename( $upload[ 'file' ] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $upload[ 'file' ]
        );

        if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
            $success = false;
            $error = 'File Uploaded But Do not have any ID associated';
        }

        $response = array(
            'success' => $success,
            'error' => $error,
            'url' => $upload['url'],
            'attachment_id' => $attachment_id,
        );
        return $response;
    }
}