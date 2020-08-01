<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$post = 0;
if (isset ( $_POST ['post'] )) {
    $post = ( int ) $_POST ['post'];
} else {
    echo "No blog post provided";
    exit ();
}

$sql = "UPDATE `blog_details` SET `active` = '1' WHERE `id` = '$post';";
mysqli_query ( $conn->db, $sql );

require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/social-media.php";
$sm = new SocialMedia ();
$sm->generateRSS ();
$sm->publishBlogToTwitter ( $post );

$conn->disconnect ();
exit ();