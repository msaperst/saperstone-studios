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
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = $_POST ['id'];
} else {
    echo "Album id is required!";
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$result = mysqli_query ( $db, $sql );
$album_info = mysqli_fetch_assoc ( $result );

system ( "../scripts/make-thumbs.sh $id " . $album_info ['location'] . " > /dev/null 2>&1 &" );