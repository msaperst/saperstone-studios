<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = new Album($_POST['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($album, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

// empty out our old cart for this image
$sql = new Sql ();
$sql->executeStatement("DELETE FROM `cart` WHERE `user` = '{$systemUser->getId()}' AND `album` = '{$album->getId()}' and `image` = '{$image->getId()}'");

// for each product, add it back in
if (isset ($_POST ['products']) && is_array($_POST ['products'])) {
    foreach ($_POST ['products'] as $product => $count) {
        $product = ( int )$product;
        $count = ( int )$count;
        $sql->executeStatement("INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '{$systemUser->getId()}', '{$album->getId()}', '{$image->getId()}', '$product', '$count');");
    }
}

echo $sql->getRow("SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '{$systemUser->getId()}';")['total'];
$sql->disconnect();
exit ();