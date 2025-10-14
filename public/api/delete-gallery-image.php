<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_POST['gallery'])) {
        throw new BadGalleryException("Gallery id is required");
    }
    $gallery = Gallery::withId($_POST['gallery']);
    if (!isset ($_POST['image'])) {
        throw new BadImageException("Image id is required");
    }
    $image = new Image($gallery, $_POST['image']);
    $image->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();