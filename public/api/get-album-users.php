<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrieveGetInt('id', 'Album id');
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

$users = $sql->getRows( "SELECT * FROM albums_for_users WHERE album = $id" );
echo json_encode ( $users );
$sql->disconnect ();
exit ();