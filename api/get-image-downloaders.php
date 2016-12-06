<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['album'] )) {
    $album = ( int ) $_GET ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['image'] )) {
    $image = ( int ) $_GET ['image'];
} else {
    echo "Image is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `download_rights` WHERE `album` = '$album' AND `image` = '$image';";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();