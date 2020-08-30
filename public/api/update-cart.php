<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

// empty out our old cart for this image
$sql = new Sql ();
$sql = "DELETE FROM `cart` WHERE `user` = '{$systemUser->getId()}';";
mysqli_query($conn->db, $sql);

// for each product, add it back in
if (isset ($_POST ['images']) && is_array($_POST ['images'])) {
    foreach ($_POST ['images'] as $image) {
        $album = (int)$image ['album'];
        $image = (int)$image ['image'];
        $product = (int)$image ['product'];
        $count = (int)$image ['count'];
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '{$systemUser->getId()}', '$album', '$image', '$product', '$count');";
        mysqli_query($conn->db, $sql);
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '{$systemUser->getId()}';";
$result = $sql->getRow($sql);
echo $result ['total'];

$conn->disconnect();
exit ();