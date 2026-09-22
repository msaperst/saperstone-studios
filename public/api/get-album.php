<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrieveGetString('id', 'Album id'));
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

$albumInfo = $album->getDataBasic();
$albumInfo['imageCount'] = (int)$album->getDataArray()['images'];
$albumInfo['needsThumbnails'] = $album->needsThumbnails();
$albumInfo ['date'] = substr($albumInfo ['date'], 0, 10);
if ($albumInfo ['code'] == NULL) {
    $albumInfo ['code'] = "";
}
echo json_encode($albumInfo);
exit ();
