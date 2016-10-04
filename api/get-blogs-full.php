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

$sql = "SELECT * FROM `blog_details` ORDER BY `date` DESC LIMIT $start,$howMany;";
if (isset ( $_GET ['tag'] )) {
    $tag = mysqli_real_escape_string ( $conn->db, $_GET ['tag'] );
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE blog_tags.tag = '$tag'  ORDER BY `date` DESC LIMIT $start,$howMany;";
}
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $sql = "SELECT * FROM `blog_images` WHERE blog = " .$r['id'] . ";";
    $images = mysqli_query ( $conn->db, $sql );
    $contents = array();
    while ( $s = mysqli_fetch_assoc ( $images ) ) {    
        $contents[] = $s;
    }
    
    $sql = "SELECT * FROM `blog_texts` WHERE blog = " .$r['id'] . ";";
    $texts = mysqli_query ( $conn->db, $sql );
    while ( $s = mysqli_fetch_assoc ( $texts ) ) {
        $contents[] = $s;
    }
    
    foreach($contents as $content ) {
        $r['content'][$content['contentGroup']][] = $content;
    }

    $sql = "SELECT `tags`.`tag` FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = " .$r['id'] . ";";
    $texts = mysqli_query ( $conn->db, $sql );
    while ( $s = mysqli_fetch_assoc ( $texts ) ) {
        $r['tags'][] = $s;
    }
    
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();