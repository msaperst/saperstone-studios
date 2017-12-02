<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = mysqli_real_escape_string ( $conn->db, $_POST ['album'] );
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['image'] )) {
    $image = mysqli_real_escape_string ( $conn->db, $_POST ['image'] );
} else {
    echo "Image is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM `share_rights` WHERE `album` = '$album' AND `image` = '$image'";
mysqli_query ( $conn->db, $sql );

if (isset ( $_POST ['users'] )) {
    foreach ( $_POST ['users'] as $user ) {
        $user = mysqli_real_escape_string ( $conn->db, $user );
        $sql = "INSERT INTO `share_rights` ( `user`, `album`, `image` ) VALUES ( '$user', '$album', '$image' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();