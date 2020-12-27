<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $contract = Contract::withParams($_POST);
    echo $contract->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();