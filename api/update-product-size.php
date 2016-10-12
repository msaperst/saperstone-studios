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

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = intval ( $_POST ['id'] );
} else {
    echo "Id is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['size'] ) && $_POST ['size'] != "") {
    $size = mysqli_real_escape_string ( $conn->db, $_POST ['size'] );
} else {
    echo "Size is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['cost'] ) && $_POST ['cost'] != "") {
    $cost = floatval ( $_POST ['cost'] );
} else {
    echo "Cost is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['price'] ) && $_POST ['price'] != "") {
    $price = floatval ( $_POST ['price'] );
} else {
    echo "Price is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "UPDATE `products` SET `size` = '$size', `cost` = '$cost' , `price` = '$price' WHERE `products`.`id` = $id;";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();