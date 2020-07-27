<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['id'] )) {
    $id = ( int ) $_GET ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT user_logs.*, users.usr FROM user_logs LEFT JOIN users ON user_logs.user = users.id WHERE album = $id";
$result = mysqli_query ( $conn->db, $sql );
$actions = array ();
while ( $row = mysqli_fetch_assoc ( $result ) ) {
    $actions [] = $row;
}
echo json_encode ( $actions );

$conn->disconnect ();
exit ();