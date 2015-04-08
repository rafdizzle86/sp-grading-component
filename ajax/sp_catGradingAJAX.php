<?php
/**
 * AJAX-Related functions for all
 * sp_catGrading components. Functions are used
 * in front end posts.
 */

if (!class_exists("sp_catGradingAJAX")) {
    class sp_catGradingAJAX
    {
        static function init()
        {
            add_action('wp_ajax_sp_grading_save_desc', array('sp_catGradingAJAX', 'sp_grading_save_desc'));
        }

        /**
         * AJAX function that saves the description
         */
        public static function sp_grading_save_desc(){
            $nonce = $_POST['nonce'];
            if( !wp_verify_nonce($nonce, 'sp_nonce') ){
                header("HTTP/1.0 403 Security Check.");
                die('Security Check');
            }

            if( empty($_POST['compid']) ){
                header("HTTP/1.0 409 Could find component ID to udpate.");
                exit;
            }
            $comp_id = (int) $_POST['compid'];
            $content = (string) $_POST['content'];

            $sp_cat_grading = new sp_catGrading( $comp_id );
            $options = $sp_cat_grading->getOptions();
            if( !is_object( $options ) || empty( $options ) ){
                $options = new stdClass();
            }

            $options->comp_desc = $content;
            $update_success = $sp_cat_grading->setOptions( $options );

            echo json_encode( array( 'success' => $update_success ) );
        }
    }
}