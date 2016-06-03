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

if (isset ( $_GET ['id'] )) {
    $id = $_GET ['id'];
} else {
    echo "ID is not provided";
    exit ();
}

$sql = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query ( $db, $sql );
echo json_encode ( mysqli_fetch_assoc ( $result ) );
exit ();