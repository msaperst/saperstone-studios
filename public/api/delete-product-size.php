<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $product = Product::withId($api->retrievePostString('id', 'Product id'));
    $product->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
