<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['type'] ) && $_POST ['type'] != "") {
    $type = ( int ) $_POST ['type'];
} else {
    echo "Product type is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['option'] ) && $_POST ['option'] != "") {
    $option = $sql->escapeString( $_POST ['option'] );
} else {
    echo "Option is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM `product_options` WHERE `product_type` = '$type' AND `opt` = '$option';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();