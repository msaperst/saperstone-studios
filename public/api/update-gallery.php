<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_POST ['id'])) {
        throw new BadGalleryException("Gallery id is required");
    }
    $gallery = Gallery::withId($api->retrievePostString('id', 'Gallery id'));
    $gallery->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
