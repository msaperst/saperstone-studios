<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $productType = ProductType::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    echo $productType->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();