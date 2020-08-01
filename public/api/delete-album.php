<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$id = $api->retrievePostInt('id', 'Album id');
if( is_array( $id ) ) {
    echo $id['error'];
    exit();
}
$album_info = $sql->getRow( "SELECT * FROM albums WHERE id = $id;" );
if (! $album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect ();
    exit ();
}
// only admin users and uploader users who own the album can make updates
if (! ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']))) {
    header ( 'HTTP/1.0 403 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

$row = $sql->getRow( "SELECT location FROM albums WHERE id='$id';" );
$sql->executeStatement( "DELETE FROM albums WHERE id='$id';" );
$sql->executeStatement( "DELETE FROM album_images WHERE album='$id';" );
$sql->executeStatement( "DELETE FROM albums_for_users WHERE album='$id';" );
if ($row ['location'] != "") {
    system ( "rm -rf " . escapeshellarg ( "../albums/" . $row ['location'] ) );
}

$sql->disconnect ();
exit ();