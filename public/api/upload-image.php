<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

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