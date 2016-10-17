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

$user;
if (! $user->isLoggedIn ()) {
    echo "User must be logged in to create an account";
    $conn->disconnect ();
    exit ();
} else {
    $user = $user->getId ();
}

// empty out our old cart for this image
$sql = "DELETE FROM `cart` WHERE `user` = '$user';";
mysqli_query ( $conn->db, $sql );

// for each product, add it back in
if (isset ( $_POST ['images'] ) && is_array ( $_POST ['images'] )) {
    foreach ( $_POST ['images'] as $image ) {
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '$user', " . $image ['album'] . ", '" . $image ['image'] . "', '" . $image ['product'] . "', '" . $image ['count'] . "');";
        mysqli_query ( $conn->db, $sql );
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '$user';";
$result = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
echo $result ['total'];

$conn->disconnect ();
exit ();