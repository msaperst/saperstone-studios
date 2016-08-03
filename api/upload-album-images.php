<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

$id = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $id = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['album'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}
// only admin users and uploader users who own the album can make updates
if (! ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']))) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT MAX(sequence) as next FROM album_images WHERE album = '$id';";
$result = mysqli_query ( $conn->db, $sql );
$album_image_info = mysqli_fetch_assoc ( $result );
$next_seq = $album_image_info ['next'];
if (is_numeric ( $next_seq )) {
    $next_seq ++;
} else {
    $next_seq = 0;
}

$output_dir = "../albums/" . $album_info ['location'] . "/";
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
        $fileName = $_FILES ["myfile"] ["name"];
        move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"], $output_dir . $fileName );
        $ret [] = $fileName;
    } else // Multiple files, file[]
{
        $fileCount = count ( $_FILES ["myfile"] ["name"] );
        for($i = 0; $i < $fileCount; $i ++) {
            $fileName = $_FILES ["myfile"] ["name"] [$i];
            move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"] [$i], $output_dir . $fileName );
            $ret [] = $fileName;
        }
    }
    
    // add our uploaded files to
    foreach ( $ret as $img ) {
        $size = getimagesize ( $output_dir . $fileName );
        $sql = "INSERT INTO `album_images` (`album`, `title`, `sequence`, `location`, `width`, `height`) VALUES ('$id', '$img', '$next_seq', '/albums/" . $album_info ['location'] . "/$img', '" . $size [0] . "', '" . $size [1] . "');";
        mysqli_query ( $conn->db, $sql );
        $next_seq ++;
    }
    
    echo json_encode ( $ret );
}

$conn->disconnect ();
exit ();