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

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$tag = "";

if (isset ( $_POST ['tag'] ) && $_POST ['tag'] != "") {
    $tag = mysqli_real_escape_string ( $conn->db, $_POST ['tag'] );
} else {
    echo "No category was provided";
    exit ();
}

$sql = "SELECT * FROM `tags` WHERE `tag` = '$tag';";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($row ['id']) {
    echo "That category already exists";
    exit ();
}

$sql = "INSERT INTO tags ( tag ) VALUES ('$tag');";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

echo $last_id;

$conn->disconnect ();
exit ();