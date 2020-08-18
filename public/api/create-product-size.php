<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $type = ProductType::withId($_POST['type']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$size = $api->retrievePostString('size', 'Product size');
if (is_array($size)) {
    echo $size['error'];
    exit();
}

$cost = $api->retrievePostFloat('cost', 'Product cost');
if (is_array($cost)) {
    echo $cost['error'];
    exit();
}

$price = $api->retrievePostFloat('price', 'Product price');
if (is_array($price)) {
    echo $price['error'];
    exit();
}

$sql = new Sql ();
echo $sql->executeStatement("INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '{$type->getId()}', '$size', '$price', '$cost');");
$sql->disconnect();
exit ();