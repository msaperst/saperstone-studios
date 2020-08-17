<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
echo json_encode($sql->getRows("SELECT user_logs.*, albums.name FROM user_logs LEFT JOIN albums ON user_logs.album = albums.id WHERE user = {$user->getId()}"));
$sql->disconnect();
exit ();