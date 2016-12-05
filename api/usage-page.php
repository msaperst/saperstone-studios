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

$length = 1;
if (isset ( $_GET ['length'] ) && $_GET ['length'] != "") {
    $length = ( int ) $_GET ['length'];
}
$start = - 1 * $length;
if (isset ( $_GET ['start'] ) && $_GET ['start'] != "") {
    $start = ( int ) $_GET ['start'];
}

$sql = "SELECT DATE(time) as date,url,COUNT(DATE(time)) AS count FROM `usage` WHERE DATE(time) > (CURDATE() + INTERVAL $start DAY) AND DATE(time) <= (CURDATE() + INTERVAL ".($start + $length)." DAY) AND `isRobot` = 0 GROUP BY DATE(time),url;";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $date = explode ( '-', $r ['date'] );
    $response[ $r['url'] ][$date [0] . "," . $date [1] . "," . $date [2]] = $r ['count'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();