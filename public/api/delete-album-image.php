<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($api->retrievePostString('album', 'Album id'));
    $image = new Image($album, $api->retrievePostString('image', 'Image id'));
    $image->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
