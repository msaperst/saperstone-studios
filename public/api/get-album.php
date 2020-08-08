<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

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

// only admin users and uploader users who own the album can make updates
if (! ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']))) {
    header ( 'HTTP/1.0 403 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

$r = $sql->getRow( "SELECT * FROM albums WHERE id = $id;" );
$r ['date'] = substr ( $r ['date'], 0, 10 );
if ($r ['code'] == NULL) {
    $r ['code'] = "";
}
echo json_encode ( $r );
$sql->disconnect ();
exit ();