<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if (!$user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = ( int ) $_POST ['id'];
} else {
    if (! isset ( $_POST ['id'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['id'] == "") {
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
    header ( 'HTTP/1.0 403 Unauthorized' );
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