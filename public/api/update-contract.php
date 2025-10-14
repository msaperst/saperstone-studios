<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $contract = Contract::withId($_POST ['id']);
    $contract->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();