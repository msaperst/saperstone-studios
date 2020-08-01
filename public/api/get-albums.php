<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$response = array ();
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