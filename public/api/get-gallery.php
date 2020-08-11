<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrieveGetInt('id', 'Gallery id');
if (is_array($id)) {
    echo $id['error'];
    exit();
}
$gallery_info = $sql->getRow("SELECT * FROM galleries WHERE id = $id;");
if (!$gallery_info ['id']) {
    echo "Gallery id does not match any galleries";
    $sql->disconnect();
    exit ();
}

echo json_encode($sql->getRow("SELECT galleries.*, COUNT(gallery_images.gallery) AS 'images' FROM galleries LEFT JOIN gallery_images ON galleries.id = gallery_images.gallery WHERE galleries.id = $id GROUP BY galleries.id;"));
$sql->disconnect();
exit ();