<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceLoggedIn();

try {
    if (!isset ($_POST['id'])) {
        throw new BadAlbumException("Album id is required");
    }
    $album = Album::withId($_POST['id']);
    if (!$album->canUserGetData()) {
        header('HTTP/1.0 403 Unauthorized');
        exit ();
    }
    $album->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
