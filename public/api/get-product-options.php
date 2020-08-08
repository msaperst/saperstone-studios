<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

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