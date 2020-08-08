<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'role';";
$row = $sql->getRow($sql);
$enumList = explode(",", str_replace("'", "", substr($row ['COLUMN_TYPE'], 5, (strlen($row ['COLUMN_TYPE']) - 6))));

echo json_encode($enumList);

$conn->disconnect();
exit ();