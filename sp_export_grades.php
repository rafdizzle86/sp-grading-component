<?php
// Collect MySQL $_POST params
if( isset( $_POST['dbhost'] ) ){
    define( 'DB_HOST', $_POST['dbhost'] );
}else{
    // Display error
}

if( isset( $_POST['dbname'] ) ){
    define( 'DB_NAME', $_POST['dbname'] );
}else{
    // Display error
}

if( isset( $_POST['dbuser'] ) ){
    define( 'DB_USER', $_POST['dbuser'] );
}else{
    // Display error
}

if( isset( $_POST['dbpass'] ) ){
    define( 'DB_PASS', $_POST['dbpass'] );
}else{
    // Display error
}

if( isset( $_POST['wp_db_prefix'] ) ){
    define( 'WP_DB_PREFIX', $_POST['wp_db_prefix'] );
}else{
    // Display error
}

if( defined( 'DB_NAME' ) && defined( 'DB_USER' ) && defined( 'DB_PASS' ) && defined( 'WP_DB_PREFIX' ) && defined( 'DB_HOST') ){

    // Format the DB_HOST constant and see if a port is provided
    list( $db_host, $db_port ) = explode( ':', DB_HOST );

    // Connect to the WordPress DB
    $mysqli = new mysqli( $db_host, DB_USER, DB_PASS, DB_NAME, $db_port );
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

    // Get the type ID for the grading component
    $cat_comp_query = sprintf( 'SELECT * FROM %ssp_compTypes WHERE name="Grading";', $mysqli->escape_string( WP_DB_PREFIX ) );
    $cat_comp_results = $mysqli->query( $cat_comp_query );
    $grading_comp = $cat_comp_results->fetch_object();
    $grading_type_id = $grading_comp->id;

    // Look up all the components and filter by the grading typeID
    $sp_comp_query = sprintf( 'SELECT * FROM %ssp_postComponents where typeID=%d;',
        $mysqli->escape_string( WP_DB_PREFIX ),
        $mysqli->escape_string( $grading_type_id )
    );

    // Sift through results, accumulating grade results
    $post_ids = array();
    $grade_results = array();
    $sp_comp_results = $mysqli->query( $sp_comp_query );

    while( $sp_post_comp_obj = $sp_comp_results->fetch_object() ){
        if( $sp_post_comp_obj->typeID === $grading_type_id ){
            $post_ids[] = $sp_post_comp_obj->postID;
            $grade_results[ $sp_post_comp_obj->postID ] = unserialize( $sp_post_comp_obj->value );
        }
    }

    $sp_comp_results->close();

    //error_log( print_r( $grade_results, true ) );
    //error_log( print_r( implode( ',', $post_ids ), true ) );

    // Get the author and post title
    $post_query = sprintf( 'SELECT ID, post_author, post_title FROM %sposts WHERE ID in (%s)',
        $mysqli->escape_string( WP_DB_PREFIX ),
        $mysqli->escape_string( implode( ',', $post_ids ) ) );

    $post_results = $mysqli->query( $post_query );
    while( $post_obj = $post_results->fetch_object() ){

        $author_query = sprintf( 'SELECT * FROM %susermeta WHERE (meta_key=\'first_name\' OR meta_key=\'last_name\') AND user_id=%d',
            $mysqli->escape_string( WP_DB_PREFIX ),
            $mysqli->escape_string( $post_obj->post_author ) );

        $author_results = $mysqli->query( $author_query );

        while( $author_obj = $author_results->fetch_object() ){
            if( $author_obj->meta_key == 'first_name' ) {
                $grade_results[ $post_obj->ID ]->first_name = $author_obj->meta_value;
            }
            if( $author_obj->meta_key == 'last_name' ) {
                $grade_results[ $post_obj->ID ]->last_name = $author_obj->meta_value;
            }
        }
        $grade_results[ $post_obj->ID ]->post_title = $post_obj->post_title;
    }

    $author_results->close();
    $post_results->close();

    $mysqli->close();

    $default_cols = array();

    // Define column names
    $default_cols['Post Name'] = 1;
    $default_cols['Author - First Name'] = 1;
    $default_cols['Author - Last Name']  = 1;
    $default_cols['Grading Description'] = 1;
    $default_cols['Grading Comments']    = 1;

    $grading_cols = array();
    // Compile grading cols
    if( !empty( $grade_results ) ){
        foreach( $grade_results as $post_id => $grade_obj ){
            if( !empty( $grade_obj->grading_fields ) ){
                foreach( $grade_obj->grading_fields as $field_key => $field_obj ){
                    $grading_cols[ $field_obj->field_name ] = 1;
                }
            }
        }
    }

    $cols = array( array_keys( $default_cols + $grading_cols ) );

    foreach( $grade_results as $post_id => $grade_obj ){

        $grading_desc = isset( $grade_obj->grading_desc ) ? $grade_obj->grading_desc : '';
        $grading_comment = isset( $grade_obj->grading_comment ) ? $grade_obj->grading_comment : '';

        $cols[] = array(
            $grade_obj->post_title,
            $grade_obj->first_name,
            $grade_obj->last_name,
            $grading_desc,
            $grading_comment
        );
    }

    error_log( print_r( $cols, true ) );


    // output headers so that the file is downloaded rather than displayed
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=data.csv' );

    // create a file pointer connected to the output stream
    $output = fopen( 'php://output', 'w' );

    // output the column headings
    foreach( $cols as $col ) {
        fputcsv($output, $col);
    }
}