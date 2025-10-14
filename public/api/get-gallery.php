<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_GET['id'])) {
        throw new BadGalleryException("Gallery id is required");
    }
    $gallery = Gallery::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
echo json_encode($sql->getRow("SELECT title FROM galleries WHERE galleries.id = {$gallery->getId()}"));
$sql->disconnect();
exit ();