<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
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
    $filename = $api->retrievePostString('filename', 'Filename');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

//update the title and caption
$sql = new Sql();
$sql->executeStatement("UPDATE gallery_images SET title = '{$title}', caption = '{$caption}' WHERE gallery='{$gallery->getId()}' AND id='{$image->getId()}';");
$sql->disconnect();
//rename the file if it needs it
if ($filename != $image->getLocation()) {
    if (Strings::startsWith($image->getLocation(), '/')) {
        $originalFile = dirname(__DIR__) . $image->getLocation();
        $newFile = dirname(__DIR__) . $filename;
    } else {
        $originalFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . explode('/', $_SERVER ['HTTP_REFERER'])[3] . DIRECTORY_SEPARATOR . $image->getLocation();
        $newFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . explode('/', $_SERVER ['HTTP_REFERER'])[3] . DIRECTORY_SEPARATOR . $filename;
    }
    if (file_exists($originalFile) && !file_exists($newFile)) {
        rename("$originalFile", "$newFile");
        $sql = new Sql();
        $sql->executeStatement("UPDATE gallery_images SET location = '{$filename}' WHERE gallery='{$gallery->getId()}' AND id='{$image->getId()}';");
        $sql->disconnect();
    } else {
        echo "Unable to find original image to rename!";
    }
}
