<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $id = $api->retrievePostInt('id', 'Product size');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$product_details = $sql->getRow("SELECT * FROM products WHERE id = $id;");
if (!$product_details ['id']) {
    echo "Product size does not match any products";
    $sql->disconnect();
    exit ();
}

$sql->executeStatement("DELETE FROM products WHERE id='$id';");
$sql->disconnect();
exit ();