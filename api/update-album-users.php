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

if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = $_POST ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM albums_for_users WHERE album = $album";
mysqli_query ( $conn->db, $sql );

if (isset ( $_POST ['users'] )) {
    foreach ( $_POST ['users'] as $user ) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '$album' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();