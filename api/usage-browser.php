<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$noAdmin = "";
if (isset ( $_GET ['noadmin'] ) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = "SELECT usage.browser,COUNT(usage.version) as count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin GROUP BY `usage`.`browser`;";
if (isset ( $_GET ['browser'] ) && $_GET ['browser'] != "") {
    $browser = mysqli_real_escape_string ( $conn->db, $_GET ['browser'] );
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