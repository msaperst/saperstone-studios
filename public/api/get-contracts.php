<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
echo "{\"data\":" . json_encode($sql->getRows("SELECT id, link, file, signature, name, session, type, date, amount FROM contracts;")) . "}";
$sql->disconnect();
exit ();