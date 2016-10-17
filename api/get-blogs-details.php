<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

require_once "../php/sql.php";
$conn = new Sql ();
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

$active = "WHERE `active`";
$andActive = "AND blog_details.active";
if ($user->isAdmin () && isset ( $_GET ['a'] ) && $_GET ['a']) {
    $active = "";
    $andActive = "";
}
$sql = "SELECT * FROM `blog_details` $active ORDER BY `date` DESC LIMIT $start,$howMany;";
if (isset ( $_GET ['tag'] )) {
    $tag = mysqli_real_escape_string ( $conn->db, $_GET ['tag'] );
    $sql = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE blog_tags.tag = '$tag' $andActive ORDER BY `date` DESC LIMIT $start,$howMany;";
}
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";

$conn->disconnect ();
exit ();