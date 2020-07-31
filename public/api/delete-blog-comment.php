<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

if (!$user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

$comment = "";
if (isset ( $_POST ['comment'] ) && $_POST ['comment'] != "") {
    $comment = ( int ) $_POST ['comment'];
} else {
    if (! isset ( $_POST ['comment'] )) {
        echo "Comment id is required";
    } elseif ($_POST ['comment'] == "") {
        echo "Comment id can not be blank";
    } else {
        echo "Some other comment id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$blog_comment_info = $sql->getRow( "SELECT * FROM blog_comments WHERE id = $comment;" );
if (! $blog_comment_info ['id']) {
    echo "Comment id does not match any comments";
    $sql->disconnect ();
    exit ();
}

// check our user permissions
if (! ( $user->isAdmin () || $user->getId () == $blog_comment_info ['user'] ) ) {
    header ( 'HTTP/1.0 403 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

// delete our image from mysql table
$sql->executeStatement( "DELETE FROM blog_comments WHERE id='$comment';" );
$sql->disconnect ();
exit ();
?>