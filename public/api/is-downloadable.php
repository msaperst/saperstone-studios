<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();
$systemUser = User::fromSystem();

if ($systemUser->isAdmin()) {
    echo 1;
    exit ();
}

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserAccess()) {
    echo 0;
    exit();
}

if ($album->canUserGetData()) {
    echo 1;
    exit();
}

try {
    $image = new Image($album, $api->retrieveGetString('image', 'Image id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$downloadable = $sql->getRow("SELECT album FROM `download_rights` WHERE ( `user` = '{$systemUser->getIdentifier()}' OR `user` = '0' ) AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );");
if (isset($downloadable ['album'])) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();
