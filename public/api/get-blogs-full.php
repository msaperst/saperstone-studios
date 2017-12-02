<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

$response = array ();
$start = 0;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}

$sql = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ( $_GET ['tag'] )) {
    $tags = array_map ( 'intval', $_GET ['tag'] );
    $where = "( blog_tags.tag = '" . implode ( "' OR blog_tags.tag = '", $tags ) . "' )";
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE $where AND blog_details.active ORDER BY `date` DESC LIMIT $start,1;";
}
$r = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $r ['id']) {
    $conn->disconnect ();
    exit ();
}
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

$sql = "SELECT * FROM `blog_comments` WHERE blog = " . $r ['id'] . " ORDER BY date desc;";
$comments = mysqli_query ( $conn->db, $sql );
while ( $s = mysqli_fetch_assoc ( $comments ) ) {
    if (($s ['user'] != "" && $s ['user'] == $user->getId ()) || $user->isAdmin ()) {
        $s ['delete'] = true;
    }
    $r ['comments'] [] = $s;
}

echo json_encode ( $r );

$conn->disconnect ();
exit ();