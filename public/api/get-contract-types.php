<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql();
echo json_encode($sql->getEnumValues('contracts', 'type'));
$sql->disconnect();
exit ();