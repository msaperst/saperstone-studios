<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$type = $api->retrievePostInt('type', 'Product type');
if( is_array( $type ) ) {
    echo $type['error'];
    exit();
}

$option = $api->retrievePostString('option', 'Product option');
if( is_array( $option ) ) {
    echo $option['error'];
    exit();
}

$sql->executeStatement( "DELETE FROM `product_options` WHERE `product_type` = '$type' AND `opt` = '$option';" );
$sql->disconnect ();
exit ();