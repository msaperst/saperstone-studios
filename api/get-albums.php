<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php"; $user = new user();

if ( !$user->isLoggedIn() ) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

$sql;

if( $user->getRole() == "admin" ) {    
    $sql = "SELECT albums.*, COUNT(album_images.album) AS 'images' FROM albums LEFT JOIN album_images ON albums.id = album_images.album GROUP BY albums.id;";
} else {
    $id = $user->getId();
    $sql = "SELECT albums.*, COUNT(album_images.album) AS 'images' FROM albums_for_users LEFT JOIN albums ON albums_for_users.album = albums.id LEFT JOIN album_images ON albums.id = album_images.album WHERE albums_for_users.user = '$id' GROUP BY albums.id;";
}
$result = mysqli_query ( $db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $r ['date'] = substr( $r ['date'], 0, 10 );
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";
exit ();

?>