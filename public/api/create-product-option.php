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

$option = $api->retrievePostString('option', 'Product option');
if( is_array( $option ) ) {
    echo $option['error'];
    exit();
}

$sql->executeStatement( "INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('$type', '$option');" );
$sql->disconnect ();
exit ();