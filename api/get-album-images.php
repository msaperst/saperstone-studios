<?php
require_once "../php/sql.php";

//Need to put in similar check that exists in album for appropriate user

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['albumId'] )) {
    $albumId = mysqli_real_escape_string ( $db, $_GET ['albumId'] );
} else {
    $response ['err'] = "Need to provide album";
    echo json_encode ( $response );
    exit ();
}
if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

if (! array_key_exists ( "err", $response )) {
    $sql = "SELECT album_images.* FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '$albumId' ORDER BY `sequence` LIMIT $start,$howMany;";
    $result = mysqli_query ( $db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $response [] = $r;
    }
}
echo json_encode ( $response );
exit ();

?>