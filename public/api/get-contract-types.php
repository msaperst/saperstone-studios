<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'contracts' AND COLUMN_NAME = 'type';";
$row = $sql->getRow( $sql );
$enumList = explode ( ",", str_replace ( "'", "", substr ( $row ['COLUMN_TYPE'], 5, (strlen ( $row ['COLUMN_TYPE'] ) - 6) ) ) );

echo json_encode ( $enumList );

$conn->disconnect ();
exit ();