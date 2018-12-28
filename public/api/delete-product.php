<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

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