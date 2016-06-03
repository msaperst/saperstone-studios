<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

$keyword = "";
if (isset ( $_GET ['keyword'] )) {
    $keyword = mysqli_real_escape_string ( $db, $_GET ['keyword'] );
}
$keyword = $_GET ['keyword'];

$sql = "SELECT * FROM users WHERE `usr` COLLATE UTF8_GENERAL_CI LIKE '%$keyword%'";
$result = mysqli_query ( $db, $sql );
$response = array();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );