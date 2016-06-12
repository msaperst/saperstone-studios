<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

$user;
if ( !isLoggedIn() ) {
    $user = $_SERVER['REMOTE_ADDR'];
} else {
    $user = getUserId();
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
    exit ();
}

$sql = "SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($album_info ['title']) {
} else {
    echo "That image doesn't match anything";
    exit ();
}

//update our mysql database
$sql = "DELETE FROM `favorites` WHERE `user` = '$user' AND `album` = '$album' AND `image` = '$sequence';";
mysqli_query ( $db, $sql );
exit ();