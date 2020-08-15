<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$currentUser = new CurrentUser ($sql);
$api = new Api ($sql, $currentUser);
$sql->disconnect();

$api->forceAdmin();

try {
    $user = new User($_POST['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$user->delete();
exit ();