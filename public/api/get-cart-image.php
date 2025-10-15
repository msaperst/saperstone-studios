<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrieveGetString('album', 'Album id'));
    $image = new Image($album, $api->retrieveGetString('image', 'Image id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$cart = $sql->getRows("SELECT cart.product, cart.count FROM `cart` WHERE `user` = '{$systemUser->getId()}' AND `album` = '{$album->getId()}' and `image` = '{$image->getId()}'");
echo json_encode($cart);
$sql->disconnect();
exit ();
