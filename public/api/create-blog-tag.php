<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

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
    $tag = $sql->escapeString( $_POST ['tag'] );
} else {
    echo "No category was provided";
    exit ();
}

$sql = "SELECT * FROM `tags` WHERE `tag` = '$tag';";
$row = $sql->getRow( $sql );
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