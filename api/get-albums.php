<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$sql;

if ($user->isAdmin ()) {
    $sql = "SELECT * FROM albums;";
} else {
    $id = $user->getId ();
    $sql = "SELECT albums.* FROM albums_for_users LEFT JOIN albums ON albums_for_users.album = albums.id WHERE albums_for_users.user = '$id' GROUP BY albums.id;";
}
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    if ($r ['date'] != null) {
        $r ['date'] = substr ( $r ['date'], 0, 10 );
    }
    $response [] = $r;
}
echo "{\"data\":" . json_encode ( $response ) . "}";

$conn->disconnect ();
exit ();