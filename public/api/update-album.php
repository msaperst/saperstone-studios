<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

try {
    $album = Album::withId($api->retrievePostString('id', 'Album id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

// only admin users and uploader users who own the album can make updates
if (!$album->canUserGetData()) {
    header('HTTP/1.0 401 Unauthorized');
    exit ();
}
try {
    $album->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
