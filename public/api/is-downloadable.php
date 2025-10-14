<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

if ($systemUser->isAdmin()) {
    echo 1;
    exit ();
}

try {
    if (!isset ($_GET['album'])) {
        throw new BadAlbumException("Album id is required");
    }
    $album = Album::withId($_GET['album']);
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
    if (!isset ($_GET['image'])) {
        throw new BadImageException("Image id is required");
    }
    $image = new Image($album, $_GET['image']);
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
