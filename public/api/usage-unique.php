<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
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

$sql = "SELECT DATE(usage.time) as date,COUNT(DISTINCT coalesce(usage.user,''),usage.ip ) AS count FROM `usage` LEFT JOIN `users` ON `usage`.`user` <=> `users`.`id` WHERE DATE(usage.time) > (CURDATE() + INTERVAL $start DAY) AND DATE(usage.time) <= (CURDATE() + INTERVAL " . ($start + $length) . " DAY) AND `usage`.`isRobot` = 0 $noAdmin GROUP BY DATE(usage.time);";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $date = explode ( '-', $r ['date'] );
    $response [$date [0] . "," . $date [1] . "," . $date [2]] = $r ['count'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();