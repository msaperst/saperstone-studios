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
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = ( int ) $_POST ['id'];
} else {
    if (! isset ( $_POST ['id'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['id'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($album_info ['id']) {
} else {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}
// only admin users and uploader users who own the album can make updates
if ($user->getRole () == "admin" || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner'])) {
} else {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT location FROM albums WHERE id='$id';";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
$sql = "DELETE FROM albums WHERE id='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM album_images WHERE album='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM albums_for_users WHERE album='$id';";
mysqli_query ( $conn->db, $sql );

if ($row ['location'] != "") {
    system ( "rm -rf " . escapeshellarg ( "../albums/" . $row ['location'] ) );
}

$conn->disconnect ();
exit ();