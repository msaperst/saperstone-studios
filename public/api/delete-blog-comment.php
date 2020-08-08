<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$comment = $api->retrievePostInt('comment', 'Comment id');
if( is_array( $comment ) ) {
    echo $comment['error'];
    exit();
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