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

$gallery = "";
if (isset ( $_POST ['gallery'] ) && $_POST ['gallery'] != "") {
    $gallery = ( int ) $_POST ['gallery'];
} else {
    if (! isset ( $_POST ['gallery'] )) {
        echo "Gallery id is required";
    } elseif ($_POST ['gallery'] == "") {
        echo "Gallery id can not be blank";
    } else {
        echo "Some other gallery id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$gallery_info = $sql->getRow( "SELECT * FROM galleries WHERE id = $gallery;" );
if (! $gallery_info ['id']) {
    echo "Gallery id does not match any galleries";
    $sql->disconnect ();
    exit ();
}

$image = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $image = ( int ) $_POST ['image'];
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
$row = $sql->getRow( "SELECT location FROM gallery_images WHERE id='$image';" );
$sql->executeStatement( "DELETE FROM gallery_images WHERE id='$image';" );

// need to re-sequence images in mysql table
$sql->executeStatement( "SET @seq:=-1;" );
$sql->executeStatement( "UPDATE gallery_images SET sequence=(@seq:=@seq+1) WHERE gallery='$gallery' ORDER BY `sequence`;" );

// delete our image from the file system
if ($row ['location'] != "") {
    system ( "rm -f " . escapeshellarg ( "../" . $row ['location'] ) );
    $parts = explode ( "/", $row ['location'] );
    $full = array_splice ( $parts, count ( $parts ) - 1, 0, "full" );
    system ( "rm -f " . escapeshellarg ( "../" . implode ( "/", $parts ) ) );
}

$sql->disconnect ();
exit ();