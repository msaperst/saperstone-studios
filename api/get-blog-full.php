<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

$response = array ();
$post = 0;

if (isset ( $_GET ['post'] )) {
    $post = ( int ) $_GET ['post'];
} else {
    echo "No blog post provided";
    exit ();
}

$sql = "SELECT * FROM `blog_details` WHERE id = $post;";
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
$tags = mysqli_query ( $conn->db, $sql );
while ( $s = mysqli_fetch_assoc ( $tags ) ) {
    $r ['tags'] [] = $s;
}
echo json_encode ( $r );

$conn->disconnect ();
exit ();