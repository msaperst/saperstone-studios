<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

// only admin users
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['post'] ) && $_POST ['post'] != "") {
    $id = ( int ) $_POST ['post'];
} else {
    if (! isset ( $_POST ['post'] )) {
        echo "Postt id is required!";
    } elseif ($_POST ['post'] != "") {
        echo "Post id cannot be blank!";
    } else {
        echo "Some other Post id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM blog_details WHERE id = $id;";
$blog_details = $sql->getRow( $sql );
if (! $blog_details ['id']) {
    echo "That ID doesn't match any posts";
    $conn->disconnect ();
    exit ();
}

// delete everything
$sql = "DELETE FROM blog_details WHERE id='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM blog_images WHERE blog='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM blog_tags WHERE blog='$id';";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM blog_texts WHERE blog='$id';";
mysqli_query ( $conn->db, $sql );

// TODO still need to delete images on disk
// TODO check if folders are empty, and delete those as well

$conn->disconnect ();
exit ();