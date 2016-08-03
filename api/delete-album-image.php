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

if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
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
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

$sequence = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $sequence = ( int ) $_POST ['image'];
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image id is required!";
    } elseif ($_POST ['image'] != "") {
        echo "Image id cannot be blank!";
    } else {
        echo "Some other Image id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

// delete our image from mysql table
$sql = "SELECT location FROM album_images WHERE album='$album' AND sequence='$sequence';";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
$sql = "DELETE FROM album_images WHERE album='$album' AND sequence='$sequence';";
mysqli_query ( $conn->db, $sql );

// need to re-sequence images in mysql table, and make these updates in cart, download rights, favorites, and share rights to match
$sql = "ALTER TABLE album_images ADD COLUMN new_sequence INT;";
mysqli_query ( $conn->db, $sql );
$sql = "SET @seq:=-1;";
mysqli_query ( $conn->db, $sql );
$sql = "UPDATE album_images SET new_sequence=(@seq:=@seq+1) WHERE album='$album';";
mysqli_query ( $conn->db, $sql );
$sql = "SELECT * FROM album_images WHERE album='$album';";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    foreach ( array (
            'cart',
            'download_rights',
            'favorites',
            'share_rights' 
    ) as $table ) {
        $sql = "UPDATE $table SET image=${r['new_sequence']} WHERE album='$album' AND image='${r['sequence']}';";
        mysqli_query ( $conn->db, $sql );
    }
    $sql = "UPDATE album_images SET sequence=${r['new_sequence']} WHERE album='$album' AND new_sequence='${r['new_sequence']}';";
    mysqli_query ( $conn->db, $sql );
}
$sql = "ALTER TABLE album_images DROP COLUMN new_sequence;";
mysqli_query ( $conn->db, $sql );

// delete our image from the file system
if ($row ['location'] != "") {
    system ( "rm -f " . escapeshellarg ( "../" . $row ['location'] ) );
    $parts = explode ( "/", $row ['location'] );
    $full = array_splice ( $parts, count ( $parts ) - 1, 0, "full" );
    system ( "rm -f " . escapeshellarg ( "../" . implode ( "/", $parts ) ) );
}

$conn->disconnect ();
exit ();