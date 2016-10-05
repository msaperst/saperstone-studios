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
if (isset ( $_POST ['gallery'] ) && $_POST ['gallery'] != "") {
    $id = ( int ) $_POST ['gallery'];
} else {
    if (! isset ( $_POST ['gallery'] )) {
        echo "Gallery ID is required!";
    } elseif ($_POST ['gallery'] != "") {
        echo "Gallery ID cannot be blank!";
    } else {
        echo "Some other Gallery ID error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM galleries WHERE id = $id;";
$gallery_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $gallery_info ['id']) {
    echo "That ID doesn't match any galleries";
    $conn->disconnect ();
    exit ();
}
// only admin users can make updates
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT MAX(sequence) as next FROM gallery_images WHERE gallery = '$id';";
$result = mysqli_query ( $conn->db, $sql );
$gallery_image_info = mysqli_fetch_assoc ( $result );
$next_seq = $gallery_image_info ['next'];
if (is_numeric ( $next_seq )) {
    $next_seq ++;
} else {
    $next_seq = 0;
}

$output_dir = "../img/" . $gallery_info ['location'] . "/";
if (isset ( $_FILES ["myfile"] )) {
    $ret = array ();
    
    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    // single file
    if (! is_array ( $_FILES ["myfile"] ["name"] )) {
        $fileName = $_FILES ["myfile"] ["name"];
        move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"], $output_dir . $fileName );
        $ret [] = $fileName;
    // Multiple files, file[]
    } else {
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
        $sql = "INSERT INTO `gallery_images` (`gallery`, `title`, `sequence`, `location`, `width`, `height`) VALUES ('$id', '$img', '$next_seq', '/img/" . $gallery_info ['location'] . "/$img', '" . $size [0] . "', '" . $size [1] . "');";
        mysqli_query ( $conn->db, $sql );
        $next_seq ++;
    }
    
    echo json_encode ( $ret );
}

$conn->disconnect ();
exit ();