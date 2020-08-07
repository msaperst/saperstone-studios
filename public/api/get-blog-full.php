<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
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
$blogDetails = $sql->getRow( "SELECT * FROM `blog_details` WHERE id = $post;" );
if (! $blogDetails ['id']) {
    echo "Blog id does not match any blogs";
    $sql->disconnect ();
    exit ();
}

$blogDetails ['date'] = date ( 'F jS, Y', strtotime ( $blogDetails ['date'] ) );

$contents = $sql->getRows( "SELECT * FROM `blog_images` WHERE blog = " . $blogDetails ['id'] . ";" );
$contents = array_merge( $contents, $sql->getRows( "SELECT * FROM `blog_texts` WHERE blog = " . $blogDetails ['id'] . ";" ) );
foreach ( $contents as $content ) {
    $blogDetails ['content'] [$content ['contentGroup']] [] = $content;
}

$blogDetails ['tags'] = $sql->getRows( "SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = " . $blogDetails ['id'] . ";" );
$blogDetails ['comments'] = $sql->getRows( "SELECT * FROM `blog_comments` WHERE blog = " . $blogDetails ['id'] . " ORDER BY date desc;" );
foreach($blogDetails ['comments'] as $key => $comment ) {
    if (($comment ['user'] != "" && $comment ['user'] == $user->getId ()) || $user->isAdmin ()) {
        $blogDetails ['comments'][$key]['delete'] = true;
    }
}

echo json_encode ( $blogDetails );
$sql->disconnect ();
exit ();