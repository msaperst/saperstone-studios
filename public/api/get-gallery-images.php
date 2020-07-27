<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
$sql = new Sql ();

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['gallery'] )) {
    $gallery = ( int ) $_GET ['gallery'];
} else {
    $response ['err'] = "Need to provide gallery";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}
if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

if (! array_key_exists ( "err", $response )) {
    $sql = "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.id = '$gallery' ORDER BY `sequence` LIMIT $start,$howMany;";
    $result = mysqli_query ( $conn->db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $response [] = $r;
    }
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();