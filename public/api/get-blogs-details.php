<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

$active = "WHERE `active`";
$andActive = "AND blog_details.active";
if ($user->isAdmin () && isset ( $_GET ['a'] ) && $_GET ['a']) {
    $active = "";
    $andActive = "";
}
$sql = "SELECT * FROM `blog_details` $active ORDER BY `date` DESC LIMIT $start,$howMany;";
if (isset ( $_GET ['tag'] )) {
    $tag = $sql->escapeString( $_GET ['tag'] );
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE blog_tags.tag = '$tag' $andActive ORDER BY `date` DESC LIMIT $start,$howMany;";
}
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";

$conn->disconnect ();
exit ();