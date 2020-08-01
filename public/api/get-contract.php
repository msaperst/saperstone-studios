<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

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
$response = $sql->getRow( $sql );
$response ['lineItems'] = array ();

$sql = "SELECT * FROM contract_line_items WHERE contract = $id;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response ['lineItems'] [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();