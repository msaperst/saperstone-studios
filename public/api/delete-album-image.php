<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $sql->disconnect ();
    exit ();
}

$album = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        echo "Album id is required";
    } elseif ($_POST ['album'] == "") {
        echo "Album id can not be blank";
    } else {
        echo "Some other album id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$album_info = $sql->getRow( "SELECT * FROM albums WHERE id = $album;" );
if (! $album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect ();
    exit ();
}

$sequence = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $sequence = ( int ) $_POST ['image'];
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image id is required";
    } elseif ($_POST ['image'] == "") {
        echo "Image id can not be blank";
    } else {
        echo "Some other image id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

// delete our image from mysql table
$row = $sql->getRow( "SELECT location FROM album_images WHERE album='$album' AND sequence='$sequence';" );
$sql->executeStatement( "DELETE FROM album_images WHERE album='$album' AND sequence='$sequence';" );
// update the image count
$sql->executeStatement( "UPDATE albums SET images = images - 1 WHERE id='$album';" );

// need to re-sequence images in mysql table, and make these updates in cart, download rights, favorites, and share rights to match
$sql->executeStatement( "ALTER TABLE album_images ADD COLUMN new_sequence INT;" );
$sql->executeStatement( "SET @seq:=-1;" );
$sql->executeStatement( "UPDATE album_images SET new_sequence=(@seq:=@seq+1) WHERE album='$album';" );
$result = $sql->getRows( "SELECT * FROM album_images WHERE album='$album';" );
foreach ( $result as $r ) {
    foreach ( array (
            'cart',
            'download_rights',
            'favorites',
            'share_rights' 
    ) as $table ) {
        $sql->executeStatement( "UPDATE $table SET image=${r['new_sequence']} WHERE album='$album' AND image='${r['sequence']}';" );
    }
    $sql->executeStatement( "UPDATE album_images SET sequence=${r['new_sequence']} WHERE album='$album' AND new_sequence='${r['new_sequence']}';" );
}
$sql->executeStatement( "ALTER TABLE album_images DROP COLUMN new_sequence;" );

// delete our image from the file system
if ($row ['location'] != "") {
    system ( "rm -f " . escapeshellarg ( "../" . $row ['location'] ) );
    $parts = explode ( "/", $row ['location'] );
    $full = array_splice ( $parts, count ( $parts ) - 1, 0, "full" );
    system ( "rm -f " . escapeshellarg ( "../" . implode ( "/", $parts ) ) );
}

$sql->disconnect ();
exit ();