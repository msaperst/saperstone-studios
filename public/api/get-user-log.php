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

$sql = new Sql ();
echo json_encode($sql->getRows("SELECT user_logs.time, user_logs.action, user_logs.what, user_logs.album, albums.name FROM user_logs LEFT JOIN albums ON user_logs.album = albums.id WHERE user = {$user->getId()}"));
$sql->disconnect();
exit ();
