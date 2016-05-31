<?php
require_once "../php/sql.php";

if (isset ( $_GET ['user'] )) {
    $user = $_GET ['user'];
} else {
    echo "User is not provided";
    exit ();
}

$sql = "SELECT * FROM albums_for_users WHERE user = $user";
$result = mysqli_query ( $db, $sql );
$response = array();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );