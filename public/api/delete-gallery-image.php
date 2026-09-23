<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
Api::requireMethod('POST');
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($api->retrievePostString('gallery', 'Gallery id'));
    $image = new Image($gallery, $api->retrievePostString('image', 'Image id'));
    $image->delete();
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}
exit ();
