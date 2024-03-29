<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($_GET['album']);
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