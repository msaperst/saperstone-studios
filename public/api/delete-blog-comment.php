<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

if (!$user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$comment = "";
if (isset ( $_POST ['comment'] ) && $_POST ['comment'] != "") {
    $comment = ( int ) $_POST ['comment'];
} else {
    if (! isset ( $_POST ['comment'] )) {
        echo "Comment id is required!";
    } elseif ($_POST ['comment'] == "") {
        echo "Comment id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM blog_comments WHERE id = $comment;";
$blog_comment_info = $sql->getRow( $sql );
if (! $blog_comment_info ['id']) {
    echo "That ID doesn't match any comments";
    $conn->disconnect ();
    exit ();
}

// check our user permissions
if (! $user->isAdmin () && $user->getId () != $blog_comment_info ['user']) {
    header ( 'HTTP/1.0 403 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

// delete our image from mysql table
$sql = "DELETE FROM blog_comments WHERE id='$comment';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();