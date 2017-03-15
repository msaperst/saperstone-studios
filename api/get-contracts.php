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
    $conn->disconnect ();
    exit ();
}

$response = array ();
$sql = "SELECT * FROM contracts;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $r['lineItems'] = array();
    
    $sql = "SELECT * FROM contract_line_items WHERE contract = {$r['id']};";
    $sesult = mysqli_query ( $conn->db, $sql );
    while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
        $r['lineItems'] [] = $s;
    }
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";

$conn->disconnect ();
exit ();