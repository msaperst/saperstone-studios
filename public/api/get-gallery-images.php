<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$response = array();
$start = 0;
$howMany = 999999999999999999;

try {
    $gallery = new Gallery($_GET['gallery']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (isset ($_GET ['start'])) {
    $start = ( int )$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = ( int )$_GET ['howMany'];
}

$response = $sql->getRows("SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.id = '{$gallery->getId()}' ORDER BY `sequence` LIMIT $start,$howMany;");
echo json_encode($response);
$sql->disconnect();
exit ();