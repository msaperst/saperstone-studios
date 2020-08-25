<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = new Gallery($_POST['gallery']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($gallery, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
$image->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();