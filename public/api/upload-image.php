<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api ();

$api->forceAdmin();

try {
    $location = $api->retrievePostString('location', 'Image location');
    $minWidth = $api->retrievePostInt('min-width', 'Image minimum width');
    $resolvedLocation = Api::resolvePublicPath($location, 'api');
    if ($resolvedLocation === null) {
        throw new BadRequestException('Image location is not valid');
    }
    $filePath = dirname($resolvedLocation);
    $fileName = basename($resolvedLocation);
    $_FILES ['myfile']['name'] = "tmp_$fileName";
    $file = new File($_FILES ['myfile']);
    $file->upload($filePath . DIRECTORY_SEPARATOR);
    $file->resize($minWidth, 0);
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}
exit();
