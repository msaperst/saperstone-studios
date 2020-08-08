<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$sql = "SELECT * FROM `cart` JOIN `album_images` ON `cart`.`image` = `album_images`.`sequence` AND `cart`.`album` = `album_images`.`album` JOIN `products` ON `cart`.`product` = `products`.`id` JOIN `product_types` ON `products`.`product_type` = `product_types`.`id` WHERE `cart`.`user` = '$user';";
$result = mysqli_query ( $conn->db, $sql );
$cart = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    unset ( $r ['cost'] );
    $sql = "SELECT opt FROM product_options WHERE product_type = '" . $r ['product_type'] . "';";
    $results = mysqli_query ( $conn->db, $sql );
    $options = array ();
    while ( $s = mysqli_fetch_assoc ( $results ) ) {
        $options [] = $s ['opt'];
    }
    $r ['options'] = $options;
    $cart [] = $r;
}
echo json_encode ( $cart );

$conn->disconnect ();
exit ();