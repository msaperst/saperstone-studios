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

$sql = "SELECT * FROM `blog_details` ORDER BY `id` DESC LIMIT $start,$howMany;";
if (isset ( $_GET ['tag'] )) {
    $tag = mysqli_real_escape_string ( $conn->db, $_GET ['tag'] );
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE blog_tags.tag = '$tag'  ORDER BY `id` DESC LIMIT $start,$howMany;";
}
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();