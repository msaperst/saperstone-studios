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

$id;
if (isset ( $_GET ['id'] )) {
    $id = ( int ) $_GET ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}

$response = array ();
$sql = "SELECT * FROM contracts WHERE id = $id;";
$response = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
$response ['lineItems'] = array ();

$sql = "SELECT * FROM contract_line_items WHERE contract = $id;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response ['lineItems'] [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();