<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_GET['id'])) {
        throw new BadUserException("User id is required");
    }
    $user = User::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($user->getDataBasic());
exit();
