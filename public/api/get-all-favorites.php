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

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT album_images.*, favorites.user, users.usr FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence LEFT JOIN users ON favorites.user = users.id;";
$result = mysqli_query ( $conn->db, $sql );

$favorites = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $favorites [$r ['album']] [] = $r;
}

$user_favs = array ();
foreach ( $favorites as $album => $favs ) {
    foreach ( $favs as $fav ) {
        $user_favs [$fav ['user']] [$album] [] = $fav;
    }
}

if (isset ( $_GET ['album'] )) {
    $album = ( int ) $_GET ['album'];
    foreach ( $user_favs as $user => $favorites ) {
        if (isset ( $favorites [$album] )) {
            $user_favs [$user] = $favorites [$album];
        } else {
            unset ( $user_favs [$user] );
        }
    }
}
echo json_encode ( $user_favs );

$conn->disconnect ();
exit ();