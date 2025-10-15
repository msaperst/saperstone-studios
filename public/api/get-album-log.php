<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($api->retrieveGetString('id', 'Album id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$actions = $sql->getRows("SELECT user_logs.time, user_logs.action, user_logs.what, users.usr FROM user_logs LEFT JOIN users ON user_logs.user = users.id WHERE album = {$album->getId()}");
echo json_encode($actions);
$sql->disconnect();
exit ();
