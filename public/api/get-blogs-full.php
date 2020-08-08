<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$start = 0;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}

$query = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ( $_GET ['tag'] )) {
    $query = "SELECT DISTINCT blog FROM blog_tags AS a1";
    $where = " WHERE ";
    for( $i = 1; $i <= sizeof( $_GET['tag'] ); $i++ ) {
        if( $i != 1 ) {
            $query .= " JOIN blog_tags AS a$i USING (blog) ";
        }
        $where .= "a$i.tag = " . $_GET['tag'][$i-1] . " AND ";
    }
    $where = substr($where, 0, -4);
    $blogs = $sql->getRows( $query . $where );

    if( sizeof( $blogs ) > 0) {
        $what = "";
        foreach ($blogs as $blog) {
            $what .= "id = " . (int)$blog['blog'] . " OR ";
        }
        $what = substr($what, 0, -3);
    } else {
        $what = 'id = -1';
    }
    $query = "SELECT DISTINCT * FROM `blog_details` WHERE `active` AND ( $what ) ORDER BY `date` DESC LIMIT $start,1;";
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
