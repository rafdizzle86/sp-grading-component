<?php
/**
 * Created by PhpStorm.
 * User: ryagudin
 * Date: 4/30/15
 * Time: 10:19 AM
 */


if (!class_exists("sp_postGradingAJAX")) {
    class sp_postGradingAJAX
    {
        static function init()
        {
            add_action('wp_ajax_sp_save_grading_desc_via_post', array('sp_postGradingAJAX', 'sp_save_grading_desc_via_post'));
            add_action('wp_ajax_sp_save_grading_comment', array('sp_postGradingAJAX', 'sp_save_grading_comment'));
        }

        /**
         * Save grading comment
         */
        function sp_save_grading_comment(){
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


            $grading_component = new sp_postGrading( $comp_id );
            $comp_vals = $grading_component->getValue();

            if( empty( $comp_vals ) ){
                $comp_vals = new stdClass();
            }

            $comp_vals->grading_comment = $content;
            $success = $grading_component->update( $comp_vals );
            wp_send_json( array( 'success' => $success ) );
            exit;
        }

        /**
         * Saves grading description via the post. Sets the "local_desc_dirty" flag to true in the component.
         */
        function sp_save_grading_desc_via_post(){
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

            $grading_component = new sp_postGrading( $comp_id );

            $comp_vals = $grading_component->getValue();

            if( empty( $comp_vals ) ){
                $comp_vals = new stdClass();
            }

            $comp_vals->dirty_desc   = true;
            $comp_vals->grading_desc = $content;

            $success = $grading_component->update( $comp_vals );

            wp_send_json( array( 'success' => $success ) );
            exit;
        }
    }
}