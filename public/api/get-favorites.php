<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

$sql = new Sql();
$results = $sql->getRows("SELECT album_images.album, album_images.sequence, album_images.location FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id WHERE favorites.user = '{$systemUser->getIdentifier()}';");
$favorites = array();
foreach ($results as $r) {
    $album = $r ['album'];
    unset($r['album']);
    $favorites [$album] [] = $r;
}

if (isset ($_GET ['album'])) {
    $album = (int)$_GET ['album'];
    if (isset ($favorites [$album])) {
        $favorites = $favorites [$album];
    } else {
        $favorites = array();
    }
}
echo json_encode($favorites);
$sql->disconnect();
exit ();