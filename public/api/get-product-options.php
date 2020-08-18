<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();

try {
    $type = ProductType::withId($_GET['type']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$options = array_column($sql->getRows("SELECT opt FROM product_options WHERE product_type = '{$type->getId()}';"), 'opt');
$sql->disconnect();
echo json_encode($options);
exit ();