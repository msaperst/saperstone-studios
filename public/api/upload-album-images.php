<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api();

$api->forceLoggedIn();

try {
    $album = Album::withId($_POST ['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

$imageLocation = DIRECTORY_SEPARATOR . "albums" . DIRECTORY_SEPARATOR . $album->getLocation() . DIRECTORY_SEPARATOR;
$outputDir = dirname(__DIR__) . $imageLocation;
try {
    $file = new File($_FILES ["myfile"]);
    $files = $file->upload($outputDir);
    $file->addToDatabase('album_images', 'albums', $album->getId(), 'album', $imageLocation);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
echo json_encode($files);
exit();