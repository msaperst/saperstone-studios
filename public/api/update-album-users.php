<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_POST ['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$sql->executeStatement("DELETE FROM albums_for_users WHERE album = {$album->getId()}");
if (isset ($_POST ['users']) && is_array($_POST ['users'])) {
    foreach ($_POST ['users'] as $user) {
        $sql->executeStatement("INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '{$album->getId()}' );");
    }
}
$sql->disconnect();
exit ();