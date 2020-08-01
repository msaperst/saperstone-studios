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
$howMany = 999999999999999999;

if (isset ( $_GET ['gallery'] )) {
    $gallery = ( int ) $_GET ['gallery'];
} else {
    $response ['err'] = "Need to provide gallery";
    echo json_encode ( $response );
    $sql->disconnect ();
    exit ();
}
if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

if (! array_key_exists ( "err", $response )) {
    $response = $sql->getRows( "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.id = '$gallery' ORDER BY `sequence` LIMIT $start,$howMany;" );
}
echo json_encode ( $response );

$sql->disconnect ();
exit ();