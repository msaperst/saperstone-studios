<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$album = $api->retrieveGetInt('album', 'Album id');
if (is_array($album)) {
    echo $album['error'];
    exit();
}
$album_info = $sql->getRow("SELECT * FROM albums WHERE id = $album;");
if (!$album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect();
    exit ();
}

$sequence = $api->retrieveGetInt('image', 'Image id');
if (is_array($sequence)) {
    echo $sequence['error'];
    exit();
}
$image_info = $sql->getRow("SELECT * FROM album_images WHERE sequence = $sequence;");
if (!$image_info ['id']) {
    echo "Image id does not match any images";
    $sql->disconnect();
    exit ();
}

$rights = $sql->getRows("SELECT * FROM `albums_for_users` LEFT JOIN `share_rights` ON `albums_for_users`.`user` = `share_rights`.`user` WHERE `albums_for_users`.`album` = '$album' AND ( `share_rights`.`album` = '$album' OR `share_rights`.`album` = '*' ) AND ( `share_rights`.`image` = '$sequence' OR `share_rights`.`image` = '*' );");
$rights = array_merge($rights, $sql->getRows("SELECT * FROM `share_rights` WHERE `user` = '0' AND ( `album` = '$album' OR `album` = '*' ) AND ( `image` = '$sequence' OR `image` = '*' );"));
echo json_encode($rights);
$sql->disconnect();
exit ();