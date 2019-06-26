<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

$user_id;
if (! $user->isLoggedIn ()) {
    $user_id = $_SERVER ['REMOTE_ADDR'];
} else {
    $user_id = $user->getId ();
}

$album = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
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

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

$sequence = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $sequence = ( int ) $_POST ['image'];
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image id is required!";
    } elseif ($_POST ['image'] != "") {
        echo "Image id cannot be blank!";
    } else {
        echo "Some other Image id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['title']) {
    echo "That image doesn't match anything";
    $conn->disconnect ();
    exit ();
}

if ($user->isLoggedIn ()) {
    // update our user records table
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Set Favorite', '$sequence', $album );" );
}

// update our mysql database
$sql = "INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('$user_id', '$album', '$sequence');";
mysqli_query ( $conn->db, $sql );
// get our new favorite count for the album
$sql = "SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '$user_id' AND `album` = '$album';";
$result = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
echo $result ['total'];

$conn->disconnect ();
exit ();