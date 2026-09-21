<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();
$systemUser = User::fromSystem();

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
    $image = new Image($album, $api->retrieveGetString('image', 'Image id'));
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$favorite = $sql->getRow("SELECT user FROM `favorites` WHERE `user` = ? AND `album` = ? AND `image` = ?", [$systemUser->getIdentifier(), $album->getId(), $image->getId()]);
if (isset($favorite ['user'])) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();
