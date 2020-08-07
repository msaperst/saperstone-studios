<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$response = array ();
$start = 0;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}

$query = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ( $_GET ['tag'] )) {
    $tags = array_map ( 'intval', $_GET ['tag'] );
    $where = "( blog_tags.tag = '" . implode ( "' OR blog_tags.tag = '", $tags ) . "' )";
    $query = "SELECT blog_details.* FROM `blog_tags` JOIN `blog_details` ON blog_tags.blog = blog_details.id WHERE $where AND blog_details.active ORDER BY `date` DESC LIMIT $start,1;";
}
$blogDetails = $sql->getRow( $query );
if (! $blogDetails ['id']) {
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
