<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $album = new Album($_GET['album']);
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
$rights = $sql->getRows("SELECT * FROM `albums_for_users` LEFT JOIN `share_rights` ON `albums_for_users`.`user` = `share_rights`.`user` WHERE `albums_for_users`.`album` = '{$album->getId()}' AND ( `share_rights`.`album` = '{$album->getId()}' OR `share_rights`.`album` = '*' ) AND ( `share_rights`.`image` = '{$image->getId()}' OR `share_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT * FROM `share_rights` WHERE `user` = '0' AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();