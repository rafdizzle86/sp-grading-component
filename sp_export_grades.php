<?php

error_log( print_r( $_POST, true ) );

// Collect MySQL $_POST params
if( isset( $_POST['dbhost'] ) ){
    define( DB_HOST, $_POST['dbhost'] );
}else{
    // Display error
}

if( isset( $_POST['dbname'] ) ){
    define( DB_NAME, $_POST['dbname'] );
}else{
    // Display error
}

if( isset( $_POST['dbuser'] ) ){
    define( DB_USER, $_POST['dbuser'] );
}else{
    // Display error
}

if( isset( $_POST['dbpass'] ) ){
    define( DB_PASS, $_POST['dbpass'] );
}else{
    // Display error
}

if( isset( $_POST['wp_db_prefix'] ) ){
    define( WP_DB_PREFIX, $_POST['wp_db_prefix'] );
}else{
    // Display error
}

if( defined( DB_NAME ) && defined( DB_USER ) && defined( DB_PASS ) && defined( WP_DB_PREFIX ) ){

    // Define column names
    $cols = array(
        'Post Name',
        'Post Author',
        'Grading Description',
        'Grading Comments'
    );

    // Connect to the WordPress DB
    $mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
    if( $mysqli->connect_error ){
        echo 'Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;
        exit(1);
    }

    // Use this instead of $connect_error if you need to ensure
    // compatibility with PHP versions prior to 5.2.9 and 5.3.0.
    if( mysqli_connect_error() ){
        echo 'Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
        exit(1);
    }

    $post_component_result = $mysqli->query( 'SELECT * FROM ' . WP_DB_PREFIX . 'sp_postComponents;' );

    error_log( print_r( $post_component_result, true ) );

    // output headers so that the file is downloaded rather than displayed
    /*
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=data.csv' );

    // create a file pointer connected to the output stream
    $output = fopen( 'php://output', 'w' );

    // output the column headings
    fputcsv( $output, array( 'Column 1', 'Column 2', 'Column 3' ) );
    */

}