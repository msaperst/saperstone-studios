<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$rights = $sql->getRows("SELECT albums_for_users.user FROM `albums_for_users` LEFT JOIN `download_rights` ON `albums_for_users`.`user` = `download_rights`.`user` WHERE `albums_for_users`.`album` = '{$album->getId()}' AND ( `download_rights`.`album` = '{$album->getId()}' OR `download_rights`.`album` = '*' ) AND ( `download_rights`.`image` = '{$image->getId()}' OR `download_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT download_rights.user FROM `download_rights` WHERE `user` = '0' AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();