<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$userId = $user->getIdentifier();
$results = $sql->getRows("SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id WHERE favorites.user = '$userId';");
$favorites = array();
foreach ($results as $r) {
    $favorites [$r ['album']] [] = $r;
}

if (isset ($_GET ['album'])) {
    $album = ( int )$_GET ['album'];
    if (isset ($favorites [$album])) {
        $favorites = $favorites [$album];
    } else {
        $favorites = array();
    }
}
echo json_encode($favorites);
$sql->disconnect();
exit ();