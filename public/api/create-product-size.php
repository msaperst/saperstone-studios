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

try {
    $size = $api->retrievePostString('size', 'Product size');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}


try {
    $cost = $api->retrievePostFloat('cost', 'Product cost');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $price = $api->retrievePostFloat('price', 'Product price');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
echo $sql->executeStatement("INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '{$type->getId()}', '$size', '$price', '$cost');");
$sql->disconnect();
exit ();