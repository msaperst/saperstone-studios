<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

$sql = new Sql();
$result = $sql->getRows("SELECT cart.product, cart.album, cart.image, album_images.title, album_images.location, product_types.name, products.product_type, products.size, products.price FROM `cart` JOIN `album_images` ON `cart`.`image` = `album_images`.`id` AND `cart`.`album` = `album_images`.`album` JOIN `products` ON `cart`.`product` = `products`.`id` JOIN `product_types` ON `products`.`product_type` = `product_types`.`id` WHERE `cart`.`user` = '{$systemUser->getId()}';");
$cart = array();
foreach ($result as $r) {
    unset ($r ['cost']);
    $r ['options'] = array_column($sql->getRows("SELECT opt FROM product_options WHERE product_type = '" . $r ['product_type'] . "';"), 'opt');
    $cart [] = $r;
}
echo json_encode($cart);
$sql->disconnect();
exit ();