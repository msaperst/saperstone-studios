<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_GET ['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$keyword = "";
if (isset ($_GET ['keyword'])) {
    $keyword = $sql->escapeString($_GET ['keyword']);
}
echo json_encode($sql->getRows("SELECT users.id, users.role, users.usr FROM users JOIN albums_for_users ON users.id = albums_for_users.user WHERE `users`.`usr` COLLATE UTF8_GENERAL_CI LIKE '%$keyword%' AND `albums_for_users`.`album` = '{$album->getId()}';"));
$sql->disconnect();
exit ();