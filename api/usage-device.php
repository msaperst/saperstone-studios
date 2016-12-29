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

$noAdmin="";
if (isset ( $_GET ['noadmin'] ) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = "SELECT SUM(usage.isTablet) as tablet,SUM(usage.isMobile) as mobile,COUNT(*)-SUM(usage.isTablet)-SUM(usage.isMobile) as desktop FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE `usage`.`isRobot` = 0 $noAdmin;";
$result = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
echo json_encode ( $result );

$conn->disconnect ();
exit ();