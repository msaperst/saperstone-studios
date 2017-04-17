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
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM product_types WHERE id='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM products WHERE product_type='$id';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();