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

$sql = "SELECT browser,COUNT(browser) as count FROM `usage` WHERE `isRobot` = 0 GROUP BY `browser`;";
if (isset ( $_GET ['browser'] ) && $_GET ['browser'] != "") {
    $sql = "SELECT version as browser,COUNT(version) as count FROM `usage` WHERE `browser` = '{$_GET['browser']}' AND `isRobot` = 0 GROUP BY `version`;";
}

$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [$r ['browser']] = $r ['count'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();