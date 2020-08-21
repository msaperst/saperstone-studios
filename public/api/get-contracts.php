<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
echo "{\"data\":" . json_encode($sql->getRows("SELECT id, link, file, signature, name, session, type, date, amount FROM contracts;")) . "}";
$sql->disconnect();
exit ();