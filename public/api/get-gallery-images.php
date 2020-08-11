<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$response = array();
$start = 0;
$howMany = 999999999999999999;

$gallery = $api->retrieveGetInt('gallery', 'Gallery id');
if (is_array($gallery)) {
    echo $gallery['error'];
    exit();
}
$gallery_info = $sql->getRow("SELECT * FROM galleries WHERE id = $gallery;");
if (!$gallery_info ['id']) {
    echo "Gallery id does not match any galleries";
    $sql->disconnect();
    exit ();
}

if (isset ($_GET ['start'])) {
    $start = ( int )$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = ( int )$_GET ['howMany'];
}

$response = $sql->getRows("SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.id = '$gallery' ORDER BY `sequence` LIMIT $start,$howMany;");
echo json_encode($response);
$sql->disconnect();
exit ();