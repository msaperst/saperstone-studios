<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$keyword = "";
if (isset ($_GET ['keyword'])) {
    $keyword = $_GET['keyword'];
}
echo json_encode($sql->getRows("SELECT users.id, users.role, users.usr FROM users JOIN albums_for_users ON users.id = albums_for_users.user WHERE `users`.`usr` COLLATE UTF8_GENERAL_CI LIKE ? AND `albums_for_users`.`album` = ?", ["%$keyword%", $album->getId()]));
$sql->disconnect();
exit ();
