<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $contract = Contract::withId($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($contract->getDataArray());
exit ();