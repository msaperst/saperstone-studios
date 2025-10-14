<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
$roles = $sql->getEnumValues('users', 'role');
echo json_encode($roles);
$sql->disconnect();
exit ();
