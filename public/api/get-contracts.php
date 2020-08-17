<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
$response = array();
foreach ($sql->getRows("SELECT * FROM contracts;") as $r) {
    $r ['lineItems'] = $sql->getRows("SELECT * FROM contract_line_items WHERE contract = {$r['id']};");
    $response [] = $r;
}
echo "{\"data\":" . json_encode($response) . "}";
$sql->disconnect();
exit ();