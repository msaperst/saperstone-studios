<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $product = Product::withId($_POST ['id']);
    $product->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();