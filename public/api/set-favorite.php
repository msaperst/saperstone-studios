<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

$userId = $systemUser->getIdentifier();

try {
    if (!isset ($_POST['album'])) {
        throw new BadAlbumException("Album id is required");
    }
    $album = Album::withId($_POST['album']);
    if (!isset ($_POST['image'])) {
        throw new BadImageException("Image id is required");
    }
    $image = new Image($album, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
if ($systemUser->isLoggedIn()) {
    // update our user records table
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$systemUser->getId()}, CURRENT_TIMESTAMP, 'Set Favorite', '{$image->getId()}', {$album->getId()} );");
}

// update our mysql database
$exists = $sql->getRowCount("SELECT * FROM `favorites` WHERE `user` = '{$userId}' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}'");
if ($exists == 0) {
    $sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('$userId', '{$album->getId()}', '{$image->getId()}');");
}
// get our new favorite count for the album
echo $sql->getRow("SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}';") ['total'];
$sql->disconnect();
exit ();
