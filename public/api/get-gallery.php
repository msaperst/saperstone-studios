<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

if (isset ( $_GET ['id'] )) {
    $id = ( int ) $_GET ['id'];
} else {
    echo "ID is not provided";
    $sql->disconnect ();
    exit ();
}

echo json_encode ( $sql->getRow( "SELECT galleries.*, COUNT(gallery_images.gallery) AS 'images' FROM galleries LEFT JOIN gallery_images ON galleries.id = gallery_images.gallery WHERE galleries.id = $id GROUP BY galleries.id;" ) );
$sql->disconnect ();
exit ();