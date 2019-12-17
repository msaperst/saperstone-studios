<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if ($user->isAdmin ()) {
    echo 1;
    $conn->disconnect ();
    exit ();
}

$user;
if (! $user->isLoggedIn ()) {
    $user = getClientIP();
} else {
    $user = $user->getId ();
}

$album = "";
if (isset ( $_GET ['album'] ) && $_GET ['album'] != "") {
    $album = ( int ) $_GET ['album'];
} else {
    if (! isset ( $_GET ['album'] )) {
        echo "Album id is required!";
    } elseif ($_GET ['album'] != "") {
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
if (isset ( $_GET ['image'] ) && $_GET ['image'] != "") {
    $sequence = ( int ) $_GET ['image'];
} else {
    if (! isset ( $_GET ['image'] )) {
        echo "Image id is required!";
    } elseif ($_GET ['image'] != "") {
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

$sql = "SELECT * FROM `download_rights` WHERE ( `user` = '$user' OR `user` = '0' ) AND ( `album` = '$album' OR `album` = '*' ) AND ( `image` = '$sequence' OR `image` = '*' );";
$downloadable = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($downloadable ['album']) {
    echo 1;
    $conn->disconnect ();
    exit ();
}

echo 0;
$conn->disconnect ();
exit ();