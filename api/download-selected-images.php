<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
} else {
    $user = $user->getId ();
}

if (isset ( $_POST ['what'] )) {
    $what = $_POST ['what'];
} else {
    $response ['err'] = "Need to provide what you desire to download";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = mysqli_real_escape_string ( $conn->db, $_POST ['album'] );
} else {
    $response ['err'] = "Need to provide album";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM `albums` WHERE id = '$album';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['name']) { // if the album doesn't exist, throw a 404 error
    $response ['err'] = "Album doesn't exist!";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

// determine what the user can download
$sql = "SELECT album_images.* FROM download_rights LEFT JOIN album_images ON download_rights.album = album_images.album AND download_rights.image = album_images.sequence WHERE download_rights.user = '$user' AND download_rights.album = '$album';";
$result = mysqli_query ( $conn->db, $sql );
$downloadable = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $downloadable [] = $r;
}

// determine what the user wants to download
if ($what == "all") {
    $sql = "SELECT album_images.* FROM album_images WHERE album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
} elseif ($what == "favorites") {
    $sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$user' AND favorites.album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
} else {
    $sql = "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
}

$available = array ();
foreach ( $desired as $file ) {
    $result = doesArrayContainFile ( $downloadable, $file );
    if ($result) {
        $available [] = $file;
    }
}
if( empty( $available ) ) {
    $response ['err'] = "There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

// for each available file, zip it, and then download it
$images = "";
foreach( $available as $image ) {
    $images .= "..".$image['location']." ";
}
if( !is_dir( "../tmp/" ) ) {
    mkdir( "../tmp/" );
}
$myFile = "../tmp/".$album_info ['name']." ".date("Y-m-d.H-i-s").".zip";
$command = `zip -j "$myFile" $images`;
$response ['file'] = $myFile;
echo json_encode( $response );

$conn->disconnect ();
exit ();

//TODO
// send email
// download full size, not small

// our function to see if an array of files contains the expected file
function doesArrayContainFile($array, $file) {
    foreach ( $array as $element ) {
        $match = true;
        foreach ( $element as $key => $value ) {
            if ($element [$key] != $file [$key]) {
                $match = false;
            }
        }
        if ($match) {
            return true;
        }
    }
    return false;
}


