<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$sql = new Sql ();
$keyword = "";
if (isset ($_GET ['keyword'])) {
    $keyword = $sql->escapeString($_GET ['keyword']);
}
echo json_encode($sql->getRows("SELECT id, role, usr FROM users WHERE `usr` COLLATE UTF8_GENERAL_CI LIKE '%$keyword%'"));
$sql->disconnect();
exit ();