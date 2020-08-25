<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $type = ProductType::withId($_POST['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
$type->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();