<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php"; $user = new user();

$user;
if (! $user->isLoggedIn ()) {
    echo "User must be logged in to create an account";
    exit ();
} else {
    $user = $user->getId ();
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

// empty out our old cart for this image
$sql = "DELETE FROM `cart` WHERE `user` = '$user' AND `album` = '$album' and `image` = '$sequence'";
mysqli_query ( $db, $sql );

// for each product, add it back in
if (isset ( $_POST ['products'] ) && is_array ( $_POST ['products'] )) {
    foreach ( $_POST ['products'] as $product => $count ) {
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '$user', '$album', '$sequence', '$product', '$count');";
        mysqli_query ( $db, $sql );
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '$user';";
$result = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
echo $result['total'];

exit ();