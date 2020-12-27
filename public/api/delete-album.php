<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceLoggedIn();

try {
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