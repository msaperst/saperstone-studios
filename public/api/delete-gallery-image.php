<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($_POST['gallery']);
    $image = new Image($gallery, $_POST['image']);
    $image->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();