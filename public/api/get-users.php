<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql ();
$users = $sql->getRows("SELECT id, usr, firstName, lastName, email, role, active, lastLogin FROM users;");
echo "{\"data\":" . json_encode($users) . "}";
$sql->disconnect();
exit ();