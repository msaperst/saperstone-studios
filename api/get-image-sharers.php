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

if (isset ( $_GET ['image'] )) {
    $image = $_GET ['image'];
} else {
    echo "Image is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `share_rights` WHERE `album` = '$album' AND `image` = '$image';";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();