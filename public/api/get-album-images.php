<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new Api();
$start = 0;
$howMany = 999999999999999999;

try {
    $album = Album::withId($api->retrieveGetString('albumId', 'Album id'));
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
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

$isAdmin = $user->isAdmin() ? 1 : 0;
$canManageData = $album->canUserGetData() ? 1 : 0;
$images = $sql->getRows("SELECT 
        album_images.id, 
        album_images.height, 
        album_images.width, 
        album_images.location, 
        album_images.title, 
        album_images.sequence, 
        IF(favorites.image IS NULL, 0, 1) AS favorite,
        IF(
            ? = 1 OR
            ? = 1 OR
            download_rights.album IS NOT NULL, 
            1, 0
        ) AS downloadable
    FROM `album_images` 
    JOIN `albums` ON album_images.album = albums.id 
    LEFT JOIN favorites ON favorites.album = album_images.album 
        AND favorites.image = album_images.id 
        AND favorites.user = ?
    LEFT JOIN download_rights ON 
        (download_rights.user = ? OR download_rights.user = '0')
        AND (download_rights.album = album_images.album OR download_rights.album = '*')
        AND (download_rights.image = album_images.id OR download_rights.image = '*')
    WHERE albums.id = ?
    ORDER BY `sequence` 
    LIMIT ?, ?", [$isAdmin, $canManageData, $user->getIdentifier(), $user->getIdentifier(), $album->getId(), $start, $howMany]);
$publicRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
$albumPath = $publicRoot . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $album->getLocation();
$thumbnailVersion = file_exists($albumPath) ? filemtime($albumPath) : false;
if ($thumbnailVersion !== false) {
    foreach ($images as &$image) {
        if ($image['location'] !== '') {
            $image['location'] .= '?v=' . $thumbnailVersion;
        }
    }
    unset($image);
}
$favoriteCount = $sql->getRow("SELECT COUNT(*) AS total FROM favorites WHERE user = ? AND album = ?", [$user->getIdentifier(), $album->getId()]);
echo json_encode(array(
    'images' => $images,
    'favoriteCount' => (int)$favoriteCount['total']
));
$sql->disconnect();
exit ();
