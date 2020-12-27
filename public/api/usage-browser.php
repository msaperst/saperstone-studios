<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$noAdmin = "";
if (isset ($_GET ['noadmin']) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = new Sql ();
$query = "SELECT usage.browser,COUNT(usage.version) as count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`browser`;";
if (isset ($_GET ['browser']) && $_GET ['browser'] != "") {
    $browser = $sql->escapeString($_GET ['browser']);
    $query = "SELECT usage.version as browser,COUNT(usage.version) as count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`browser` = '$browser' AND `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`version`;";
}
$response = array();
foreach ($sql->getRows($query) as $r) {
    $response [$r ['browser']] = $r ['count'];
}
$sql->disconnect();
echo json_encode($response);
exit ();