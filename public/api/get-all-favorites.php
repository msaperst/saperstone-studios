<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
$user_favs = array();
if (isset ($_GET ['album'])) {
    $album = (int)$_GET ['album'];
    $users = $sql->getRows("SELECT DISTINCT `favorites`.`user` FROM `favorites` WHERE `favorites`.`album` = $album;");
    foreach ($users as $user) {
        $images = $sql->getRows("SELECT album_images.sequence, album_images.location, album_images.title, users.usr FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id LEFT JOIN users ON favorites.user = users.id WHERE `favorites`.`album` = $album && `favorites`.`user` = '" . $user['user'] . "';");
        $user_favs[$user['user']] = $images;
    }
} else {
    $favorites = array();
    foreach ($sql->getRows("SELECT album_images.album, album_images.sequence, album_images.location, album_images.title, favorites.user, users.usr FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id LEFT JOIN users ON favorites.user = users.id;") as $r) {
        $album = $r ['album'];
        unset($r['album']);
        $favorites [$album] [] = $r;
    }
    foreach ($favorites as $album => $favs) {
        foreach ($favs as $fav) {
            $user = $fav ['user'];
            unset($fav['user']);
            $user_favs [$user] [$album] [] = $fav;
        }
    }
}
echo json_encode($user_favs);
$sql->disconnect();
exit ();