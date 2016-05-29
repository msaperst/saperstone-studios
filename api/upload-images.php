<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $name = $_POST ['album'];
} else {
    echo "Album id is required!";
    exit ();
}

$output_dir = "../albums/";
if(isset($_FILES["myfile"]))
{
    $ret = array();

    //	This is for custom errors;
    /*	$custom_error= array();
    $custom_error['jquery-upload-file-error']="File already exists";
    echo json_encode($custom_error);
    die();
    */
    $error =$_FILES["myfile"]["error"];
    //You need to handle  both cases
    //If Any browser does not support serializing of multiple files using FormData()
    if(!is_array($_FILES["myfile"]["name"])) //single file
    {
        $fileName = $_FILES["myfile"]["name"];
        move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$fileName);
        $ret[]= $fileName;
    }
    else  //Multiple files, file[]
    {
        $fileCount = count($_FILES["myfile"]["name"]);
        for($i=0; $i < $fileCount; $i++)
        {
            $fileName = $_FILES["myfile"]["name"][$i];
            move_uploaded_file($_FILES["myfile"]["tmp_name"][$i],$output_dir.$fileName);
            $ret[]= $fileName;
        }

    }
    echo json_encode($ret);
}