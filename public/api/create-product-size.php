<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$type = $api->retrievePostInt('type', 'Product type');
if( is_array( $type ) ) {
    echo $type['error'];
    exit();
}

$size = $api->retrievePostString('size', 'Product size');
if( is_array( $size ) ) {
    echo $size['error'];
    exit();
}

$cost = $api->retrievePostFloat('cost', 'Product cost');
if( is_array( $cost ) ) {
    echo $cost['error'];
    exit();
}

$price = $api->retrievePostFloat('price', 'Product price');
if( is_array( $price ) ) {
    echo $price['error'];
    exit();
}

echo $sql->executeStatement( "INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '$type', '$size', '$price', '$cost');" );
$sql->disconnect ();
exit ();