<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['albumId'] ) && $_GET ['albumId'] != "") {
    $albumId = ( int ) $_GET ['albumId'];
} else {
    if (! isset ( $_GET ['albumId'] )) {
        $response ['err'] = "Album id is required!";
    } elseif ($_GET ['albumId'] == "") {
        $response ['err'] = "Album id cannot be blank!";
    } else {
        $response ['err'] = "Some other Album id error occurred!";
    }
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
$album_info = $sql->getRow( $sql );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    $response ['err'] = "Album doesn't exist!";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

// if not an admin and no code exists for the album     //todo, this seems like an issue, we should ensure the code is stored
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