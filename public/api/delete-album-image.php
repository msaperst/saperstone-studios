<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_POST['album'])) {
        throw new BadAlbumException("Album id is required");
    }
    $album = Album::withId($_POST['album']);
    if (!isset ($_POST['image'])) {
        throw new BadImageException("Image id is required");
    }
    $image = new Image($album, $_POST['image']);
    $image->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
