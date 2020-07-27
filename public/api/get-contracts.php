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

$response = array ();
$sql = "SELECT * FROM contracts;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $r ['lineItems'] = array ();
    
    $sql = "SELECT * FROM contract_line_items WHERE contract = {$r['id']};";
    $sesult = mysqli_query ( $conn->db, $sql );
    while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
        $r ['lineItems'] [] = $s;
    }
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";

$conn->disconnect ();
exit ();