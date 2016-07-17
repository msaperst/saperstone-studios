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
    $user = $_SERVER ['REMOTE_ADDR'];
} else {
    $user = $user->getId ();
}

$sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$user';";
$result = mysqli_query ( $db, $sql );

$favorites = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $favorites [$r ['album']] [] = $r;
}

if (isset ( $_GET ['album'] )) {
    $favorites = $favorites [$_GET ['album']];
}
echo json_encode ( $favorites );
exit ();