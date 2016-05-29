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

if (isset ( $_POST ['id'] )) {
    $id = $_POST ['id'];
} else {
    echo "ID is not provided";
    exit ();
}

$sql = "SELECT location FROM albums WHERE id='$id';";
$row = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
$sql = "DELETE FROM albums WHERE id='$id';";
mysqli_query ( $db, $sql );
$sql = "DELETE FROM album_images WHERE album='$id';";
mysqli_query ( $db, $sql );

if( $row ['location'] != "" ) {
    system("rm -rf ".escapeshellarg("../albums/" . $row ['location']));
}
exit ();

?>