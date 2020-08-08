<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$noAdmin = "";
if (isset ($_GET ['noadmin']) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = "SELECT SUM(usage.isTablet) as tablet,SUM(usage.isMobile) as mobile,COUNT(*)-SUM(usage.isTablet)-SUM(usage.isMobile) as desktop FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin;";
$result = $sql->getRow($sql);
echo json_encode($result);

$conn->disconnect();
exit ();