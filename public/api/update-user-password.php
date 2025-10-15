<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($api->retrievePostString('id', 'User id'));
    $user->updatePassword($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
