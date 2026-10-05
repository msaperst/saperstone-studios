<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($api->retrievePostString('gallery', 'Gallery id'));
    $image = new Image($gallery, $api->retrievePostString('image', 'Image id'));
    $title = $api->retrievePostString('title', 'Title');
    $caption = "";
    if (isset ($_POST ['caption']) && $_POST ['caption'] != "") {
        $caption = $api->retrievePostString('caption', 'Caption');
    }
    $requestedLocation = str_replace('\\', '/', $api->retrievePostString('filename', 'Filename'));
    if (str_contains($requestedLocation, "\0")) {
        throw new BadRequestException('Filename is not valid');
    }
    $filename = basename($requestedLocation);
    if ($filename === '' || $filename === '.' || $filename === '..') {
        throw new BadRequestException('Filename is not valid');
    }
    File::validateImageExtension($filename);

    $currentLocation = str_replace('\\', '/', $image->getLocation());
    $currentDirectory = dirname($currentLocation);
    $newLocation = ($currentDirectory === '.' ? '' : rtrim($currentDirectory, '/') . '/') . $filename;
    if ($requestedLocation !== $newLocation) {
        throw new BadRequestException('Filename is not valid');
    }
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

//update the title and caption
$sql = new Sql();
$sql->executeStatement("UPDATE gallery_images SET title = ?, caption = ? WHERE gallery = ? AND id = ?", [$title, $caption, $gallery->getId(), $image->getId()]);
$sql->disconnect();
//rename the file if it needs it
if ($newLocation !== $currentLocation) {
    if (Strings::startsWith($currentLocation, '/')) {
        $originalFile = Api::resolvePublicPath($currentLocation);
        $newFile = $originalFile === null
            ? null
            : dirname($originalFile) . DIRECTORY_SEPARATOR . $filename;
    } elseif (isset($_SERVER['HTTP_REFERER'])) {
        $refererPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        $section = explode('/', trim((string)$refererPath, '/'))[0] ?? '';
        $originalFile = Api::resolvePublicPath('/' . $section . '/' . $currentLocation);
        $newFile = $originalFile === null
            ? null
            : dirname($originalFile) . DIRECTORY_SEPARATOR . $filename;
    } else {
        http_response_code(500);
        echo "Unable to find original image to rename!";
        exit();
    }

    if ($originalFile === null || $newFile === null) {
        http_response_code(400);
        echo "Filename is not valid";
        exit();
    }

    if (file_exists($originalFile) && !file_exists($newFile)) {
        rename($originalFile, $newFile);
        $sql = new Sql();
        $sql->executeStatement("UPDATE gallery_images SET location = ? WHERE gallery = ? AND id = ?", [$newLocation, $gallery->getId(), $image->getId()]);
        $sql->disconnect();
    } else {
        http_response_code(500);
        echo "Unable to find original image to rename!";
        exit();
    }
}
exit();
