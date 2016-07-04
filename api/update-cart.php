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
if (! isLoggedIn ()) {
    echo "User must be logged in to create an account";
    exit ();
} else {
    $user = getUserId ();
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

// empty out our old cart for this image
$sql = "DELETE FROM `cart` WHERE `user` = '$user' AND `album` = '$album';";
mysqli_query ( $db, $sql );

// for each product, add it back in
if (isset ( $_POST ['images'] ) && is_array ( $_POST ['images'] )) {
    foreach ( $_POST ['images'] as $image ) {
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '$user', '$album', '" . $image ['image'] . "', '" . $image ['product'] . "', '" . $image ['count'] . "');";
        mysqli_query ( $db, $sql );
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '$user';";
$result = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
echo $result ['total'];

exit ();