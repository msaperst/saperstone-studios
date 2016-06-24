<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () == "admin") {
    echo 1;
    exit ();
}

$user;
if (! isLoggedIn ()) {
    $user = $_SERVER ['REMOTE_ADDR'];
} else {
    $user = getUserId ();
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
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($album_info ['id']) {
} else {
    echo "That ID doesn't match any albums";
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
    exit ();
}

$sql = "SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($album_info ['title']) {
} else {
    echo "That image doesn't match anything";
    exit ();
}

$sql = "SELECT * FROM `download_rights` WHERE `user` = '$user' AND `album` = '$album' AND `image` = '$sequence';";
$downloadable = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($downloadable ['user']) {
    echo 1;
    exit ();
}
echo 0;
exit ();