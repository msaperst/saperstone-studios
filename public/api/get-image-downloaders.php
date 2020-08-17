<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$api->forceAdmin();

try {
    $album = new Album($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

try {
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

$rights = $sql->getRows("SELECT * FROM `albums_for_users` LEFT JOIN `download_rights` ON `albums_for_users`.`user` = `download_rights`.`user` WHERE `albums_for_users`.`album` = '{$album->getId()}' AND ( `download_rights`.`album` = '{$album->getId()}' OR `download_rights`.`album` = '*' ) AND ( `download_rights`.`image` = '{$image->getId()}' OR `download_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT * FROM `download_rights` WHERE `user` = '0' AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();