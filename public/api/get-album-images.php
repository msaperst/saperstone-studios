<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new Api();
$start = 0;
$howMany = 999999999999999999;

try {
    $album = Album::withId($api->retrieveGetString('albumId', 'Album id'));
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
    exit();
}

if (!$album->canUserAccess()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

if (isset ($_GET ['start'])) {
    $start = (int)$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = (int)$_GET ['howMany'];
}

$sql = new Sql();
$user = User::fromSystem();

$images = $sql->getRows("SELECT album_images.id, album_images.height, album_images.width, album_images.location, album_images.title, album_images.sequence, IF(favorites.image IS NULL, 0, 1) AS favorite FROM `album_images` JOIN `albums` ON album_images.album = albums.id LEFT JOIN favorites ON favorites.album = album_images.album AND favorites.image = album_images.id AND favorites.user = '" . $user->getIdentifier() . "' WHERE albums.id = '{$album->getId()}' ORDER BY `sequence` LIMIT $start,$howMany;");
$publicRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
foreach ($images as &$image) {
    $thumbLocation = $image['location'];
    $fullLocation = $thumbLocation;
    $fullPath = dirname($publicRoot . $thumbLocation) . DIRECTORY_SEPARATOR . 'full' . DIRECTORY_SEPARATOR . basename($thumbLocation);
    if (file_exists($fullPath)) {
        $fullLocation = dirname($thumbLocation) . '/full/' . basename($thumbLocation);
    }
    $image['thumbLocation'] = $thumbLocation;
    $image['fullLocation'] = $fullLocation;
    $image['hasFull'] = $fullLocation !== $thumbLocation;
}
unset($image);
$favoriteCount = $sql->getRow("SELECT COUNT(*) AS total FROM favorites WHERE user = '" . $user->getIdentifier() . "' AND album = '{$album->getId()}';");
echo json_encode(array(
    'images' => $images,
    'favoriteCount' => (int)$favoriteCount['total']
));
$sql->disconnect();
exit ();
