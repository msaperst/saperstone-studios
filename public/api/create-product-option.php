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
    $option = $api->retrievePostString('option', 'Product option');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$sql->executeStatement("INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('{$type->getId()}', '$option');");
$sql->disconnect();
exit ();