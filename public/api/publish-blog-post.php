<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$post = 0;
if (isset ( $_POST ['post'] )) {
    $post = ( int ) $_POST ['post'];
} else {
    echo "No blog post provided";
    exit ();
}

$sql = "UPDATE `blog_details` SET `active` = '1' WHERE `id` = '$post';";
mysqli_query ( $conn->db, $sql );

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/social-media.php";
$sm = new SocialMedia ();
$sm->generateRSS ();
$sm->publishBlogToTwitter ( $post );

$conn->disconnect ();
exit ();