<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$id = $api->retrievePostInt('id', 'Product size');
if (is_array($id)) {
    echo $id['error'];
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