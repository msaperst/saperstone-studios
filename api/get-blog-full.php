<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

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

$sql = "SELECT `*` FROM `blog_comments` WHERE blog = " . $r ['id'] . " ORDER BY date desc;";
$comments = mysqli_query ( $conn->db, $sql );
while ( $s = mysqli_fetch_assoc ( $comments ) ) {
    if( ( $s['user'] != "" && $s['user'] == $user->getId() ) || $user->isAdmin() ) {
        $s['delete'] = true;
    }
    $r ['comments'] [] = $s;
}

echo json_encode ( $r );

$conn->disconnect ();
exit ();