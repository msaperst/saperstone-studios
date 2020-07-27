<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
$sql = new Sql ();

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