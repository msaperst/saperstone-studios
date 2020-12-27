<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_GET['album']);
    if( $_GET['image'] == '*' ) {
        $image = '*';
    } else {
        $image = (new Image($album, $_GET['image']))->getId();
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$rights = $sql->getRows("SELECT albums_for_users.user FROM `albums_for_users` LEFT JOIN `download_rights` ON `albums_for_users`.`user` = `download_rights`.`user` WHERE `albums_for_users`.`album` = '{$album->getId()}' AND ( `download_rights`.`album` = '{$album->getId()}' OR `download_rights`.`album` = '*' ) AND ( `download_rights`.`image` = '$image' OR `download_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT download_rights.user FROM `download_rights` WHERE `user` = '0' AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '$image' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();