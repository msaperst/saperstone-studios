<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

if (file_exists ( "../php/user.php" )) {
    include_once "../php/user.php";
    include_once "../php/sql.php";
}
if (file_exists ( "../../php/user.php" )) {
    include_once "../../php/sql.php";
    include_once "../../php/sql.php";
}
$user = new User ();
$conn = new Sql ();
$conn->connect ();

// only admin users and uploader users who own the album can make updates
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_FILES ["myfile"] )) {
    $ret = array ();
    
    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    // single file
    if (! is_array ( $_FILES ["myfile"] ["name"] )) {
        $location = mysqli_real_escape_string ( $conn->db, $_POST ['location'] );
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