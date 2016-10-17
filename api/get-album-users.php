<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

if (isset ( $_GET ['album'] )) {
    $album = $_GET ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums_for_users WHERE album = $album";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();