<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();
$systemUser = User::fromSystem();

$userId = $systemUser->getIdentifier();

try {
    $album = Album::withId($api->retrievePostString('album', 'Album id'));
    $image = new Image($album, $api->retrievePostString('image', 'Image id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
if ($systemUser->isLoggedIn()) {
    // update our user records table
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( $userId, CURRENT_TIMESTAMP, 'Unset Favorite', '{$image->getId()}', {$album->getId()} );");
}

// update our mysql database
$sql->executeStatement("DELETE FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}';");
// get our new favorite count for the album
echo $sql->getRowCount("SELECT * FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}';");
$sql->disconnect();
exit ();
