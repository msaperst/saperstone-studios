<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (isset ( $_GET ['code'] )) {
    $code = $_GET ['code'];
} else {
    echo "Code is not provided!";
    exit ();
}

$sql = "SELECT * FROM albums WHERE code = '$code';";
$r = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($r ['id']) {
    echo $r ['id'];
} else {
    echo "That code doesn't match any albums";
    exit ();
}

if (isset ( $_GET ['albumAdd'] ) && $_GET ['albumAdd'] == 1) {
    $sql = "SELECT * FROM albums_for_users WHERE user = '" . getUserId () . "' AND album = '" . $r ['id'] . "';";
    $s = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
    if ($s ['user']) {
    } else {
    $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '" . getUserId () . "', '" . $r ['id'] . "' );";
    mysqli_query ( $db, $sql );
    }
}
exit ();