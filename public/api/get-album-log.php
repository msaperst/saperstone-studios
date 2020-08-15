<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

try {
    $album = new Album($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

$actions = $sql->getRows("SELECT user_logs.*, users.usr FROM user_logs LEFT JOIN users ON user_logs.user = users.id WHERE album = {$album->getId()}");
echo json_encode($actions);
$sql->disconnect();
exit ();
?>