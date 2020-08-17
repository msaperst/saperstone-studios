<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$type = $api->retrievePostInt('type', 'Product type');
if (is_array($type)) {
    echo $type['error'];
    exit();
}

$option = $api->retrievePostString('option', 'Product option');
if (is_array($option)) {
    echo $option['error'];
    exit();
}

$sql = new Sql ();
$sql->executeStatement("INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('$type', '$option');");
$sql->disconnect();
exit ();