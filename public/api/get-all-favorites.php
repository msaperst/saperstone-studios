<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$user_favs = array ();
if (isset ( $_GET ['album'] )) {
    $album = ( int ) $_GET ['album'];
    $users = $sql->getRows( "SELECT DISTINCT `favorites`.`user` FROM `favorites` WHERE `favorites`.`album` = $album;" );
    foreach( $users as $user ) {
        $images = $sql->getRows( "SELECT album_images.*, favorites.user, users.usr FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence LEFT JOIN users ON favorites.user = users.id WHERE `favorites`.`album` = $album && `favorites`.`user` = '" . $user['user'] . "';" );
        $user_favs[$user['user']] = $images;
    }
} else {
    $favorites = array ();
    foreach ( $sql->getRows( "SELECT album_images.*, favorites.user, users.usr FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence LEFT JOIN users ON favorites.user = users.id;" ) as $r ) {
        $favorites [$r ['album']] [] = $r;
    }
    foreach ( $favorites as $album => $favs ) {
        foreach ( $favs as $fav ) {
            $user_favs [$fav ['user']] [$album] [] = $fav;
        }
    }
}
echo json_encode( $user_favs );
$sql->disconnect ();
exit ();
?>