<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$start = 0;
$howMany = 999999999999999999;

try {
    $album = Album::withId($_GET['albumId']);
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
$images = $sql->getRows("SELECT album_images.height, album_images.width, album_images.location, album_images.title, album_images.sequence FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '{$album->getId()}' ORDER BY `sequence` LIMIT $start,$howMany;");
echo json_encode($images);
$sql->disconnect();
exit ();