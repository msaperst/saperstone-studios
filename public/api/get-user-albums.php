<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$currentUser = new CurrentUser ($sql);
$api = new Api ($sql, $currentUser);

$api->forceAdmin();

try {
    $user = new User($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

echo json_encode($sql->getRows("SELECT * FROM albums_for_users WHERE user = {$user->getId()}"));
$sql->disconnect();
exit ();