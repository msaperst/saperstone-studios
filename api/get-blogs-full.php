<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

$response = array ();
$start = 0;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}

$sql = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ( $_GET ['tag'] )) {
    $tag = mysqli_real_escape_string ( $conn->db, $_GET ['tag'] );
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE blog_tags.tag = '$tag' AND blog_details.active ORDER BY `date` DESC LIMIT $start,1;";
}
$r = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
$r ['date'] = date ( 'F jS, Y', strtotime ( $r ['date'] ) );
$sql = "SELECT * FROM `blog_images` WHERE blog = " . $r ['id'] . ";";
$images = mysqli_query ( $conn->db, $sql );
$contents = array ();
while ( $s = mysqli_fetch_assoc ( $images ) ) {
    $contents [] = $s;
}

$sql = "SELECT * FROM `blog_texts` WHERE blog = " . $r ['id'] . ";";
$texts = mysqli_query ( $conn->db, $sql );
while ( $s = mysqli_fetch_assoc ( $texts ) ) {
    $contents [] = $s;
}

foreach ( $contents as $content ) {
    $r ['content'] [$content ['contentGroup']] [] = $content;
}

$sql = "SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = " . $r ['id'] . ";";
$texts = mysqli_query ( $conn->db, $sql );
while ( $s = mysqli_fetch_assoc ( $texts ) ) {
    $r ['tags'] [] = $s;
}

echo json_encode ( $r );

$conn->disconnect ();
exit ();