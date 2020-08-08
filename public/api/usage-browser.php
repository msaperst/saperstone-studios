<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$noAdmin = "";
if (isset ( $_GET ['noadmin'] ) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = "SELECT usage.browser,COUNT(usage.version) as count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`browser`;";
if (isset ( $_GET ['browser'] ) && $_GET ['browser'] != "") {
    $browser = $sql->escapeString( $_GET ['browser'] );
    $sql = "SELECT usage.version as browser,COUNT(usage.version) as count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`browser` = '$browser' AND `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`version`;";
}

$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [$r ['browser']] = $r ['count'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();