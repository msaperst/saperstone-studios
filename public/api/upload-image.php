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
    $filePath = realpath(dirname($resolvedLocation));
    if ($filePath === false) {
        throw new BadRequestException('Image location is not valid');
    }

    $projectRoot = dirname(__DIR__, 2);
    $publicRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . 'public');
    $contentRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . 'content');
    $isPublicPath = $publicRoot !== false
        && ($filePath === $publicRoot || str_starts_with($filePath, $publicRoot . DIRECTORY_SEPARATOR));
    $isContentPath = $contentRoot !== false
        && ($filePath === $contentRoot || str_starts_with($filePath, $contentRoot . DIRECTORY_SEPARATOR));
    if (!$isPublicPath && !$isContentPath) {
        throw new BadRequestException('Image location is not valid');
    }

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
