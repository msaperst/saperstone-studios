<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $contract = Contract::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($contract->getDataBasic());
exit ();