<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$api = new Api ();

$api->forceAdmin();

try {
    $productType = ProductType::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo $productType->create();
exit ();