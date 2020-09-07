<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$response = array();
$start = 0;
$howMany = 999999999999999999;

try {
    $gallery = Gallery::withId($_GET['gallery']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (isset ($_GET ['start'])) {
    $start = (int)$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = (int)$_GET ['howMany'];
}

$sql = new Sql();
$response = $sql->getRows("SELECT gallery_images.id, gallery_images.sequence, gallery_images.height, gallery_images.width, gallery_images.location, gallery_images.title FROM `gallery_images` WHERE gallery_images.gallery = '{$gallery->getId()}' ORDER BY `sequence` LIMIT $start,$howMany;");
echo json_encode($response);
$sql->disconnect();
exit ();