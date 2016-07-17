<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/sql.php";
include_once "../php/user.php"; $user = new user();

// Need to put in similar check that exists in album for appropriate user

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['albumId'] )) {
    $albumId = mysqli_real_escape_string ( $db, $_GET ['albumId'] );
} else {
    $response ['err'] = "Need to provide album";
    echo json_encode ( $response );
    exit ();
}
if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

$sql = "SELECT * FROM `albums` WHERE id = '$albumId';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if (! $album_info ['name']) { // if the album doesn't exist, throw a 404 error
    $response ['err'] = "Album doesn't exist!";
    echo json_encode ( $response );
    exit ();
}

if ($user->getRole () != "admin" && $album_info ['code'] == "") { // if not an admin and no exists for the album
    if (! $user->isLoggedIn ()) { // if not logged in, throw an error
        $response ['err'] = "You are not authorized to view this album";
        echo json_encode ( $response );
        exit ();
    } else {
        $sql = "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "';";
        $result = mysqli_query ( $db, $sql );
        $albums = array ();
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            $albums [] = $r ['album'];
        }
        if (! in_array ( $albumId, $albums )) { // if not in album list
            $response ['err'] = "You are not authorized to view this album";
            echo json_encode ( $response );
            exit ();
        }
    }
}

if (! array_key_exists ( "err", $response )) {
    $sql = "SELECT album_images.* FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '$albumId' ORDER BY `sequence` LIMIT $start,$howMany;";
    $result = mysqli_query ( $db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $response [] = $r;
    }
}
echo json_encode ( $response );
exit ();

?>