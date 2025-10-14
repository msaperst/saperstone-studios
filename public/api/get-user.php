<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($api->retrieveGetString('id', 'User id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($user->getDataBasic());
exit();
