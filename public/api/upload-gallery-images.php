<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($_POST ['gallery']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$imageLocation = $gallery->getImageLocation();
$outputDir = dirname(__DIR__) . $imageLocation;

try {
    $file = new File($_FILES ["myfile"]);
    $files = $file->upload($outputDir);
    $file->resize('1140', '760');
    $file->addToDatabase('gallery_images', 'galleries', $gallery->getId(), 'gallery', $imageLocation);
} catch (Exception $e) {
    echo json_encode($e->getMessage());
    exit();
}
echo json_encode($files);
exit();