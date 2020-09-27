<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $location = $api->retrievePostString('location', 'Image location');
    $minWidth = $api->retrievePostInt('min-width', 'Image minimum width');
    $filePath = dirname($location);
    $fileName = basename($location);
    $_FILES ['myfile']['name'] = "tmp_$fileName";
    $file = new File($_FILES ['myfile']);
    $file->upload($filePath . DIRECTORY_SEPARATOR);
    $file->resize($minWidth, 0);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit();