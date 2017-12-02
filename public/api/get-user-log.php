<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
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

$sql = "SELECT user_logs.*, albums.name FROM user_logs LEFT JOIN albums ON user_logs.album = albums.id WHERE user = $id";
$result = mysqli_query ( $conn->db, $sql );
$actions = array ();
while ( $row = mysqli_fetch_assoc ( $result ) ) {
    $actions [] = $row;
}
echo json_encode ( $actions );

$conn->disconnect ();
exit ();