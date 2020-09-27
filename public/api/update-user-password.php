<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_POST['id']);
    $user->updatePassword($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();