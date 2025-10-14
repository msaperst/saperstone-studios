<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrieveGetString('id', 'Album id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

$albumInfo = $album->getDataBasic();
$albumInfo ['date'] = substr($albumInfo ['date'], 0, 10);
if ($albumInfo ['code'] == NULL) {
    $albumInfo ['code'] = "";
}
echo json_encode($albumInfo);
exit ();
