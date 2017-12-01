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

if (isset ( $_GET ['type'] ) && $_GET ['type'] != "") {
    $type = ( int ) $_GET ['type'];
} else {
    echo "Product type is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT opt FROM product_options WHERE product_type = '$type';";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r ['opt'];
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();