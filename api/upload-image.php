<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

// only admin users and uploader users who own the album can make updates
if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_FILES ["myfile"] )) {
    $ret = array ();
    
    // This is for custom errors;
    /*
     * $custom_error= array();
     * $custom_error['jquery-upload-file-error']="File already exists";
     * echo json_encode($custom_error);
     * die();
     */
    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    if (! is_array ( $_FILES ["myfile"] ["name"] )) // single file
{
        $filePath = dirname( $_POST ['location'] );
        $fileName = basename( $_POST ['location'] );
        move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"], "$filePath/tmp_$fileName" );
        $ret [] = $fileName;
        
        if( getimagesize( "$filePath/tmp_$fileName" )[0] < ($_POST['min-width']-1) ) {
            echo "Image size is too small, please upload one with a minimum width of ".$_POST['min-width'];
        }
    } else // Multiple files, file[]
{
        header ( 'HTTP/1.0 500 Bad Inputs' );
    }
} else {
    header ( 'HTTP/1.0 500 Bad Inputs' );
}

exit ();