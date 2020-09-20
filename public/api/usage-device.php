<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$noAdmin = "";
if (isset ($_GET ['noadmin']) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = new Sql ();
echo json_encode($sql->getRow("SELECT SUM(usage.isTablet) as tablet,SUM(usage.isMobile) as mobile,COUNT(*)-SUM(usage.isTablet)-SUM(usage.isMobile) as desktop FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin;"));
$sql->disconnect();
exit ();