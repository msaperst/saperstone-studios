<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceLoggedIn();

try {
    $user = User::fromSystem();
    $user->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();