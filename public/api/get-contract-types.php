<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
echo json_encode($sql->getEnumValues('contracts', 'type'));
$sql->disconnect();
exit ();