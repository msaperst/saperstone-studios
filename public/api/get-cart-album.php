<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$album = $api->retrieveGetInt('album', 'Album id');
if (is_array($album)) {
    echo $album['error'];
    exit();
}
$album_info = $sql->getRow("SELECT * FROM albums WHERE id = $album;");
if (!$album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect();
    exit ();
}

$result = $sql->getRows("SELECT * FROM `cart` JOIN `album_images` ON `cart`.`image` = `album_images`.`sequence` AND `cart`.`album` = `album_images`.`album` JOIN `products` ON `cart`.`product` = `products`.`id` JOIN `product_types` ON `products`.`product_type` = `product_types`.`id` WHERE `cart`.`user` = '{$user->getId()}' AND `cart`.`album` = '$album';");
$cart = array();
foreach ($result as $r) {
    unset ($r ['cost']);
    $results = $sql->getRows("SELECT opt FROM product_options WHERE product_type = '" . $r ['product_type'] . "';");
    $options = array();
    foreach ($results as $s) {
        $options [] = $s ['opt'];
    }
    $r ['options'] = $options;
    $cart [] = $r;
}
echo json_encode($cart);
$sql->disconnect();
exit ();