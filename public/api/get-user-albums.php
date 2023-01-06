<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_GET['user']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
echo json_encode($sql->getRows("SELECT albums_for_users.album FROM albums_for_users WHERE user = {$user->getId()}"));
$sql->disconnect();
exit ();