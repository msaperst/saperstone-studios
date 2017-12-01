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

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['albumId'] )) {
    $albumId = ( int ) $_GET ['albumId'];
} else {
    $response ['err'] = "Need to provide album";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}
if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

$sql = "SELECT * FROM `albums` WHERE id = '$albumId';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    $response ['err'] = "Album doesn't exist!";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

// if not an admin and no exists for the album
if (! $user->isAdmin () && $album_info ['code'] == "") {
    // if not logged in, throw an error
    if (! $user->isLoggedIn ()) {
        $response ['err'] = "You are not authorized to view this album";
        echo json_encode ( $response );
        $conn->disconnect ();
        exit ();
    } else {
        $sql = "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "';";
        $result = mysqli_query ( $conn->db, $sql );
        $albums = array ();
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            $albums [] = $r ['album'];
        }
        // if not in album list
        if (! in_array ( $albumId, $albums )) {
            $response ['err'] = "You are not authorized to view this album";
            echo json_encode ( $response );
            $conn->disconnect ();
            exit ();
        }
    }
}

if (! array_key_exists ( "err", $response )) {
    $sql = "SELECT album_images.* FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '$albumId' ORDER BY `sequence` LIMIT $start,$howMany;";
    $result = mysqli_query ( $conn->db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $response [] = $r;
    }
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();