<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$gallery = $api->retrievePostInt('gallery', 'Gallery id');
if( is_array( $gallery ) ) {
    echo $gallery['error'];
    exit();
}
$gallery_info = $sql->getRow( "SELECT * FROM galleries WHERE id = $gallery;" );
if (! $gallery_info ['id']) {
    echo "Gallery id does not match any galleries";
    $sql->disconnect ();
    exit ();
}

$image = $api->retrievePostInt('image', 'Image id');
if( is_array( $image ) ) {
    echo $image['error'];
    exit();
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