<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$user = new User ();
$sql = new Sql ();

// only admin users and uploader users who own the album can make updates
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    exit ();
}

if (isset ( $_FILES ["myfile"] )) {
    $ret = array ();
    
    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    // single file
    if (! is_array ( $_FILES ["myfile"] ["name"] )) {
        $location = $sql->escapeString( $_POST ['location'] );
        $filePath = dirname ( $location );
        $fileName = basename ( $location );
        move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"], "$filePath/tmp_$fileName" );
        $ret [] = $fileName;
        
        if (getimagesize ( "$filePath/tmp_$fileName" ) [0] < ($_POST ['min-width'] - 1)) {
            echo "Image size is too small, please upload one with a minimum width of " . $_POST ['min-width'];
        }
        // Multiple files, file[]
    } else {
        header ( 'HTTP/1.0 500 Bad Inputs' );
    }
} else {
    header ( 'HTTP/1.0 500 Bad Inputs' );
}

exit ();