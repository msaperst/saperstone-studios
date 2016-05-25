<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

$sql = "SELECT albums.*, COUNT(album_images.album) AS images FROM `album_images` JOIN `albums` ON album_images.album = albums.id GROUP BY album_images.album;";
$result = mysqli_query ( $db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $r ['date'] = substr( $r ['date'], 0, 10 );
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";
exit ();

?>