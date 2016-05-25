<?php
require_once"../php/sql.php";

$response = array ();
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['gallery'] )) {
    $gallery = mysqli_real_escape_string ( $db, $_GET ['gallery'] );
} else {
    $response ['err'] = "Need to provide gallery";
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
    $sql = "SELECT * FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.name = 'portrait-maternity' ORDER BY `sequence` LIMIT $start,$howMany;";
    $result = mysqli_query ( $db, $sql );
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $response [] = $r;
    }
}
echo json_encode ( $response );
exit ();

?>