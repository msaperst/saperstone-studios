<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($user->getDataBasic());
exit();