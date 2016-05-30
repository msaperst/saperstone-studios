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
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

// $sql = "SHOW COLUMNS FROM users WHERE Field = 'role';";
$sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'role';";
$row = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
$enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));

echo json_encode( $enumList );
exit ();