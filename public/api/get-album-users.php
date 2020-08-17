<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $album = new Album($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$users = $sql->getRows("SELECT * FROM albums_for_users WHERE album = {$album->getId()}");
echo json_encode($users);
$sql->disconnect();
exit ();