<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$response = array ();
$post = 0;

$post = $api->retrieveGetInt('post', 'Blog id');
if( is_array( $post ) ) {
    echo $post['error'];
    exit();
}
$post_info = $sql->getRow( "SELECT * FROM `blog_details` WHERE id = $post;" );
if (! $post_info ['id']) {
    echo "Blog id does not match any blogs";
    $sql->disconnect ();
    exit ();
}

$post_info ['date'] = date ( 'F jS, Y', strtotime ( $post_info ['date'] ) );

$contents = $sql->getRows( "SELECT * FROM `blog_images` WHERE blog = " . $post_info ['id'] . ";" );
$contents = array_merge( $contents, $sql->getRows( "SELECT * FROM `blog_texts` WHERE blog = " . $post_info ['id'] . ";" ) );
foreach ( $contents as $content ) {
    $post_info ['content'] [$content ['contentGroup']] [] = $content;
}

$post_info ['tags'] = $sql->getRows( "SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = " . $post_info ['id'] . ";" );
$post_info ['comments'] = $sql->getRows( "SELECT * FROM `blog_comments` WHERE blog = " . $post_info ['id'] . " ORDER BY date desc;" );
foreach( $post_info ['comments'] as $key => $comment ) {
    if (($comment ['user'] != "" && $comment ['user'] == $user->getId ()) || $user->isAdmin ()) {
        $post_info ['comments'][$key]['delete'] = true;
    }
}

echo json_encode ( $post_info );
$sql->disconnect ();
exit ();