<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$api = new Api ();

$api->forceAdmin();

try {
    $type = ProductType::withId($_POST['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$type->delete();
exit ();