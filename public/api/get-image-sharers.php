<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
    $image = $api->retrieveGetString('image', 'Image id');
    if ($image != '*') {
        $image = (new Image($album, $image))->getId();
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$rights = $sql->getRows("SELECT albums_for_users.user FROM `albums_for_users` LEFT JOIN `share_rights` ON `albums_for_users`.`user` = `share_rights`.`user` WHERE `albums_for_users`.`album` = '{$album->getId()}' AND ( `share_rights`.`album` = '{$album->getId()}' OR `share_rights`.`album` = '*' ) AND ( `share_rights`.`image` = '$image' OR `share_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT share_rights.user FROM `share_rights` WHERE `user` = '0' AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '$image' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();
