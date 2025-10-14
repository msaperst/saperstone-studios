<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

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

$sql = new Sql();
$cart = $sql->getRows("SELECT cart.product, cart.count FROM `cart` WHERE `user` = '{$systemUser->getId()}' AND `album` = '{$album->getId()}' and `image` = '{$image->getId()}'");
echo json_encode($cart);
$sql->disconnect();
exit ();
