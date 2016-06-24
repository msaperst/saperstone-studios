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
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = $_POST ['album'];
} else {
    echo "Album is not provided";
    exit ();
}

if (isset ( $_POST ['image'] )) {
    $image = $_POST ['image'];
} else {
    echo "Image is not provided";
    exit ();
}

$sql = "DELETE FROM `download_rights` WHERE `album` = '$album' AND `image` = '$image'";
mysqli_query ( $db, $sql );

if( isset( $_POST['users'] ) ) {
    foreach ( $_POST ['users'] as $user ) {
        $sql = "INSERT INTO `download_rights` ( `user`, `album`, `image` ) VALUES ( '$user', '$album', '$image' );";
        mysqli_query ( $db, $sql );
    }
}