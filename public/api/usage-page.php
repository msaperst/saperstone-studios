<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
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

$noAdmin = "";
if (isset ( $_GET ['noadmin'] ) && $_GET ['noadmin'] == "1") {
    $noAdmin = " AND ( `users`.`role` != 'admin' OR `users`.`role` is NULL )";
}

$sql = "SELECT DATE(usage.time) as date,usage.url,COUNT(DATE(usage.time)) AS count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE DATE(usage.time) > (CURDATE() + INTERVAL $start DAY) AND DATE(usage.time) <= (CURDATE() + INTERVAL " . ($start + $length) . " DAY) AND `usage`.`isRobot` = 0 $noAdmin GROUP BY DATE(usage.time),usage.url;";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $date = explode ( '-', $r ['date'] );
    $response [$r ['url']] [$date [0] . "," . $date [1] . "," . $date [2]] = $r ['count'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();