<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

try {
    if (!isset ($_GET['album'])) {
        throw new BadAlbumException("Album id is required");
    }
    $album = Album::withId($_GET['album']);
    if (!isset ($_GET['image'])) {
        throw new BadImageException("Image id is required");
    }
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$favorite = $sql->getRow("SELECT user FROM `favorites` WHERE `user` = '{$systemUser->getIdentifier()}' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}';");
if (isset($favorite ['user'])) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();
