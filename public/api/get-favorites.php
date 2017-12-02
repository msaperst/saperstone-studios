<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

$user;
if (! $user->isLoggedIn ()) {
    $user = $_SERVER ['REMOTE_ADDR'];
} else {
    $user = $user->getId ();
}

$sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$user';";
$result = mysqli_query ( $conn->db, $sql );

$favorites = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $favorites [$r ['album']] [] = $r;
}

if (isset ( $_GET ['album'] )) {
    $album = ( int ) $_GET ['album'];
    if (isset ( $favorites [$album] )) {
        $favorites = $favorites [$album];
    } else {
        $favorites = array ();
    }
}
echo json_encode ( $favorites );

$conn->disconnect ();
exit ();