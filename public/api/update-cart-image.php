<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($_POST['album']);
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
        try {
            $product = Product::withId($product);
            $count = getInt('count', $count);
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

function getInt($variable, $param) {
    if (isset ($param) && $param != "") {
        return (int)$param;
    } else {
        if (!isset ($param)) {
            throw new Exception(ucfirst($variable) . " is required");
        } else {
            throw new Exception(ucfirst($variable) . " can not be blank");
        }
    }
}