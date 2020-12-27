<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$users = $sql->getRows("SELECT albums_for_users.user FROM albums_for_users WHERE album = {$album->getId()}");
echo json_encode($users);
$sql->disconnect();
exit ();