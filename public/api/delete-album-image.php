<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
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
$album_info = $sql->getRow( $sql );
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
$row = $sql->getRow( $sql );
$sql = "DELETE FROM album_images WHERE album='$album' AND sequence='$sequence';";
mysqli_query ( $conn->db, $sql );

// update the image count
$sql = "UPDATE albums SET images = images - 1 WHERE id='$album';";
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