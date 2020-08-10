<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrieveGetInt('id', 'Contract id');
if (is_array($id)) {
    echo $id['error'];
    exit();
}
$contract_info = $sql->getRow("SELECT * FROM contracts WHERE id = $id;");
if (!$contract_info ['id']) {
    echo "Contract id does not match any contracts";
    $sql->disconnect();
    exit ();
}

$contract_info ['lineItems'] = $sql->getRows( "SELECT * FROM contract_line_items WHERE contract = $id;" );
echo json_encode($contract_info);
$sql->disconnect();
exit ();