<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_POST['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$user->delete();
exit ();