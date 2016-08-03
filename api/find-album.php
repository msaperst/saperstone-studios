<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (isset ( $_GET ['code'] )) {
    $code = $_GET ['code'];
} else {
    echo "Code is not provided!";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE code = '$code';";
$r = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($r ['id']) {
    echo $r ['id'];
} else {
    echo "That code doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['albumAdd'] ) && $_GET ['albumAdd'] == 1) {
    $sql = "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "' AND album = '" . $r ['id'] . "';";
    $s = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
    if (!$s ['user']) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '" . $user->getId () . "', '" . $r ['id'] . "' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();