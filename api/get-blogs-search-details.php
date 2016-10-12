<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}
if (isset ( $_GET ['searchTerm'] )) {
    $search = $_GET ['searchTerm'];
} else {
    exit ();
}

$sql = "SELECT * FROM (SELECT id AS blog FROM `blog_details` WHERE `title` LIKE '%$search%' AND `active` UNION ALL SELECT blog FROM `blog_texts` WHERE `text` LIKE '%$search%') AS x GROUP BY `blog` DESC LIMIT $start,$howMany;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $sql = "SELECT * FROM `blog_details` WHERE `id` = '" . $r ['blog'] . "';";
    $response [] = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();