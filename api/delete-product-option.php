<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['type'] ) && $_POST ['type'] != "") {
    $type = $_POST ['type'];
} else {
    echo "Product type is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['option'] ) && $_POST ['option'] != "") {
    $option = $_POST ['option'];
} else {
    echo "Option is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM `product_options` WHERE `product_type` = '$type' AND `opt` = '$option';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();