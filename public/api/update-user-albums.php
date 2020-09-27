<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_POST ['user']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$albums = array();
if (isset ($_POST ['albums']) && is_array($_POST ['albums'])) {
    foreach ($_POST ['albums'] as $album) {
        try {
            $albums[] = Album::withId($album);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }
}

$sql = new Sql ();
$sql->executeStatement("DELETE FROM albums_for_users WHERE user = {$user->getId()}");

foreach ($albums as $album) {
    $sql->executeStatement("INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '{$user->getId()}', '{$album->getId()}' );");
}
$sql->disconnect();
exit ();