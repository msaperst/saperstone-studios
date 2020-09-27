<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($_POST ['album']);
    $image = new Image($album, $_POST ['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$sql->executeStatement("DELETE FROM `download_rights` WHERE `album` = '{$album->getId()}' AND `image` = '{$image->getId()}'");

if (isset ($_POST ['users']) && is_array($_POST ['users'])) {
    foreach ($_POST ['users'] as $user) {
        $user = $sql->escapeString($user);
        $sql->executeStatement("INSERT INTO `download_rights` ( `user`, `album`, `image` ) VALUES ( '$user', '{$album->getId()}', '{$image->getId()}' );");
    }
}
$sql->disconnect();
exit ();
