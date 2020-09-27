<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$noAdmin = "";
if (isset ($_GET ['noadmin']) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = new Sql ();
$response = array();
foreach ($sql->getRows("SELECT `usage`.width, `usage`.height, count(*) AS count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`width` != '' AND `usage`.`height` != '' AND `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`width`, `usage`.`height`;") as $r) {
    $response [$r ['width'] . "x" . $r ['height']] = $r ['count'];
}
echo json_encode($response);
$sql->disconnect();
exit ();