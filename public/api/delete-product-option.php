<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $type = ProductType::withId($api->retrievePostString('type', 'Product id'));
    $option = $api->retrievePostString('option', 'Product option');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$sql->executeStatement("DELETE FROM `product_options` WHERE `product_type` = ? AND `opt` = ?", [$type->getId(), $option]);
$sql->disconnect();
exit ();
