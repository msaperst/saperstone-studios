<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($_POST['gallery']);
    $image = new Image($gallery, $_POST['image']);
    $title = $api->retrievePostString('title', 'Title');
    $caption = "";
    if (isset ($_POST ['caption'])) {
        $sql = new Sql();
        $caption = $sql->escapeString($_POST ['caption']);
        $sql->disconnect();
    }
    $filename = $api->retrievePostString('filename', 'Filename');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
//rename the file
rename( dirname(__DIR__) . $image->getLocation(), dirname(__DIR__) . $filename);
//update the database
$sql->executeStatement("UPDATE gallery_images SET title = '{$title}', caption = '{$caption}', location = '{$filename}' WHERE gallery='{$gallery->getId()}' AND id='{$image->getId()}';");
$sql->disconnect();


