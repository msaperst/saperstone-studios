<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$response = array ();
$query;
if ($user->isAdmin ()) {
    $query = "SELECT * FROM albums;";
} else {
    $id = $user->getId ();
    $query = "SELECT albums.* FROM albums_for_users LEFT JOIN albums ON albums_for_users.album = albums.id WHERE albums_for_users.user = '$id' GROUP BY albums.id;";
}
$sanitizedAlbums = array();
$albums = $sql->getRows( $query );
foreach( $albums as $album ) {
    if ($album ['date'] != null) {
        $album ['date'] = substr ( $album ['date'], 0, 10 );
    }
    array_push( $sanitizedAlbums, $album );
}
echo "{\"data\":" . json_encode ( $sanitizedAlbums ) . "}";
$sql->disconnect ();
exit ();