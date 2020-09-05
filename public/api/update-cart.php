<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

// empty out our old cart for this image
$sql = new Sql ();
$sql->executeStatement("DELETE FROM `cart` WHERE `user` = '{$systemUser->getId()}';");

// for each product, add it back in
if (isset ($_POST ['images']) && is_array($_POST ['images'])) {
    foreach ($_POST ['images'] as $item) {
        try {
            $album = Album::withId(getInt('album', $item));
            $image = new Image($album, getInt('image', $item));
            $product = Product::withId(getInt('product', $item));
            $count = getInt('count', $item);
        } catch (Exception $e) {
            echo $e->getMessage();
            $sql->disconnect();
            exit();
        }
        $sql->executeStatement("INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '{$systemUser->getId()}', '{$album->getId()}', '{$image->getId()}', '{$product->getId()}', '$count');");
    }
}
$total = $sql->getRow("SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '{$systemUser->getId()}';")['total'];
if ($total == NULL) {
    echo 0;
} else {
    echo $total;
}
$sql->disconnect();
exit ();

function getInt($variable, $params) {
    if (isset ($params [$variable]) && $params [$variable] != "") {
        return (int)$params [$variable];
    } else {
        if (!isset ($params [$variable])) {
            throw new Exception(ucfirst($variable) . " is required");
        } else {
            throw new Exception(ucfirst($variable) . " can not be blank");
        }
    }
}