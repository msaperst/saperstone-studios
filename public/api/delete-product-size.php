<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrievePostInt('id', 'Product size');
if( is_array( $id ) ) {
    echo $id['error'];
    exit();
}
$product_details = $sql->getRow( "SELECT * FROM products WHERE id = $id;" );
if (! $product_details ['id']) {
    echo "Product size does not match any products";
    $sql->disconnect ();
    exit ();
}

$sql->executeStatement( "DELETE FROM products WHERE id='$id';" );
$sql->disconnect ();
exit ();