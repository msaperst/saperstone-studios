<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

$length = 1;
if (isset ($_GET ['length']) && $_GET ['length'] != "") {
    $length = (int)$_GET ['length'];
}
$start = -1 * $length;
if (isset ($_GET ['start']) && $_GET ['start'] != "") {
    $start = (int)$_GET ['start'];
}

$noAdmin = "";
if (isset ($_GET ['noadmin']) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = new Sql ();
$response = array();
foreach ($sql->getRows("SELECT DATE(usage.time) as date,COUNT(DATE(usage.time)) AS count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE DATE(usage.time) > (CURDATE() + INTERVAL $start DAY) AND DATE(usage.time) <= (CURDATE() + INTERVAL " . ($start + $length) . " DAY) AND `usage`.`isRobot` = 0 $noAdmin GROUP BY DATE(usage.time);") as $r) {
    $date = explode('-', $r ['date']);
    $response [$date [0] . "," . $date [1] . "," . $date [2]] = $r ['count'];
}
echo json_encode($response);
$sql->disconnect();
exit ();