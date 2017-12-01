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

$comment = "";
if (isset ( $_POST ['comment'] ) && $_POST ['comment'] != "") {
    $comment = ( int ) $_POST ['comment'];
} else {
    if (! isset ( $_POST ['comment'] )) {
        echo "Comment id is required!";
    } elseif ($_POST ['comment'] != "") {
        echo "Comment id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM blog_comments WHERE id = $comment;";
$blog_comment_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $blog_comment_info ['id']) {
    echo "That ID doesn't match any comments";
    $conn->disconnect ();
    exit ();
}

// check our user permissions
if (! $user->isAdmin () && $user->getId () != $blog_comment_info ['user']) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

// delete our image from mysql table
$sql = "DELETE FROM blog_comments WHERE id='$comment';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();