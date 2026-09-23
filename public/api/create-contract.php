<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
Api::requireMethod('POST');
$api = new Api ();

$api->forceAdmin();

try {
    $contract = Contract::withParams($_POST);
    echo $contract->create();
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}
exit ();