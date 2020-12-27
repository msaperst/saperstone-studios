<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $type = ProductType::withId($_POST['id']);
    $type->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();