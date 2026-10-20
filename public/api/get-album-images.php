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
        album_images.caption,
        album_images.sequence, 
        IF(EXISTS (
            SELECT 1 FROM favorites
            WHERE favorites.album = album_images.album
                AND favorites.image = album_images.id
                AND favorites.user = ?
        ), 1, 0) AS favorite,
        IF(
            ? = 1 OR
            ? = 1 OR
            EXISTS (
                SELECT 1 FROM download_rights
                WHERE (download_rights.user = ? OR download_rights.user = '0')
                    AND (download_rights.album = album_images.album OR download_rights.album = '*')
                    AND (download_rights.image = album_images.id OR download_rights.image = '*')
            ),
            1, 0
        ) AS downloadable
    FROM `album_images`
    JOIN `albums` ON album_images.album = albums.id
    WHERE albums.id = ?
    ORDER BY `sequence`, album_images.id
    LIMIT ?, ?", [$user->getIdentifier(), $isAdmin, $canManageData, $user->getIdentifier(), $album->getId(), $start, $howMany]);
$publicRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
$albumPath = $publicRoot . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $album->getLocation();
$thumbnailVersion = file_exists($albumPath) ? filemtime($albumPath) : false;
if ($thumbnailVersion !== false) {
    foreach ($images as &$image) {
        if ($image['location'] !== '') {
            $location = $image['location'];
            $directory = str_replace('\\\\', '/', dirname($location));
            $filename = basename($location);
            $image['thumbnail1600'] = $location . '?v=' . $thumbnailVersion;
            // Older albums and freshly uploaded images may not have the smaller
            // derivatives yet. Fall back to the existing protected root image.
            foreach ([400, 800, 1200] as $size) {
                $thumbnailPath = $albumPath . DIRECTORY_SEPARATOR . 'thumbs'
                    . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $filename;
                $image['thumbnail' . $size] = is_file($thumbnailPath) && filesize($thumbnailPath) > 0
                    ? $directory . '/thumbs/' . $size . '/' . $filename . '?v=' . $thumbnailVersion
                    : $image['thumbnail1600'];
            }
            // Keep location as the largest protected derivative for backwards compatibility.
            $image['location'] = $image['thumbnail1600'];
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
