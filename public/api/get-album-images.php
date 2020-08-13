<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$start = 0;
$howMany = 999999999999999999;

try {
    $album = new Album($_GET['albumId']);
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
    $sql->disconnect();
    exit();
}

if (!$album->canUserAccess()) {
    header('HTTP/1.0 403 Unauthorized');
    $sql->disconnect();
    exit ();
}

if (isset ($_GET ['start'])) {
    $start = ( int )$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = ( int )$_GET ['howMany'];
}

$images = $sql->getRows("SELECT album_images.* FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '{$album->getId()}' ORDER BY `sequence` LIMIT $start,$howMany;");
echo json_encode($images);
$sql->disconnect();
exit ();