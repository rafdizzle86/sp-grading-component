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
            add_action('wp_ajax_sp_grading_save_field', array('sp_catGradingAJAX', 'sp_grading_save_field'));
            add_action('wp_ajax_sp_grading_set_field_name', array('sp_catGradingAJAX', 'sp_grading_set_field_name'));
            add_action('wp_ajax_sp_grading_delete_field', array('sp_catGradingAJAX', 'sp_grading_delete_field'));
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

        /**
         * Deletes a grading field
         */
        function sp_grading_delete_field(){
            $nonce = $_POST['nonce'];
            if( !wp_verify_nonce($nonce, 'sp_nonce') ){
                header("HTTP/1.0 403 Security Check.");
                die('Security Check');
            }

            if( !isset( $_POST['compid'] ) ){
                header("HTTP/1.0 409 Could find component ID to udpate.");
                exit;
            }

            if( !isset( $_POST['fieldKey'] ) ){
                header("HTTP/1.0 409 Could find field key to udpate.");
                exit;
            }

            $comp_id = (int) $_POST['compid'];
            $field_key = (int) $_POST['fieldKey'];

            // Get the grading component
            $sp_cat_grading = new sp_catGrading( $comp_id );
            $options = $sp_cat_grading->getOptions();

            if( !isset( $options->fields[ $field_key ] ) ){
                header("HTTP/1.0 409 Could find field key is not associated with any field.");
                exit;
            }else{
                // Remove the field
                unset( $options->fields[ $field_key ] );
                $success = $sp_cat_grading->setOptions( $options );
                if( $success !== false ){
                    wp_send_json( array( $success => $success ) );
                }else{
                    header("HTTP/1.0 409 Could not set field name!");
                    exit;
                }
            }
        }

        /**
         * Given a key, sets a grading field's name
         */
        function sp_grading_set_field_name(){
            $nonce = $_POST['nonce'];
            if( !wp_verify_nonce($nonce, 'sp_nonce') ){
                header("HTTP/1.0 403 Security Check.");
                die('Security Check');
            }

            if( !isset( $_POST['compid'] ) ){
                header("HTTP/1.0 409 Could find component ID to udpate.");
                exit;
            }

            if( !isset( $_POST['fieldKey'] ) ){
                header("HTTP/1.0 409 Could find field key to udpate.");
                exit;
            }

            $comp_id = (int) $_POST['compid'];
            $field_key = (int) $_POST['fieldKey'];
            $new_name = (string) $_POST['fieldName'];

            $sp_cat_grading = new sp_catGrading( $comp_id );
            $options = $sp_cat_grading->getOptions();
            if( !is_object( $options ) || empty( $options ) ){
                $options = new stdClass();
            }

            if( !isset( $options->fields[ $field_key ] ) ){
                header("HTTP/1.0 409 Could find field key is not associated with any field.");
                exit;
            }else{
                $options->fields[ $field_key ]->field_name = $new_name;
                $success = $sp_cat_grading->setOptions( $options );
                if( $success !== false ){
                    wp_send_json( array( $success => $success ) );
                }else{
                    header("HTTP/1.0 409 Could not set field name!");
                    exit;
                }
            }

            exit;

        }

        /**
         * AJAX function that saves a new field
         */
        public static function sp_grading_save_field(){
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

            $sp_cat_grading = new sp_catGrading( $comp_id );
            $options = $sp_cat_grading->getOptions();
            if( !is_object( $options ) || empty( $options ) ){
                $options = new stdClass();
            }

            if( empty( $options->fields ) ){
                $options->fields = array();
            }

            $field_name = (string) $_POST[ 'fieldname'];
            $field_type = (string) $_POST[ 'field_type' ];

            $field_obj = new stdClass();
            $field_obj->field_name = $field_name;
            $field_obj->field_type = $field_type;
            $field_key = array_push( $options->fields, $field_obj ) - 1;

            // Add field
            $sp_cat_grading->setOptions( $options );

            $sp_cat_grading->render_field( $field_obj, $field_key );
            exit;
        }
    }
}