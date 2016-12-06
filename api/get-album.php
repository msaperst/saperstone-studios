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

$id = "";
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
    $id = ( int ) $_GET ['id'];
} else {
    if (! isset ( $_GET ['id'] )) {
        echo "Album id is required!";
    } elseif ($_GET ['id'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

// only admin users and uploader users who own the album can make updates
if (! ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']))) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT albums.*, COUNT(album_images.album) AS 'images' FROM albums LEFT JOIN album_images ON albums.id = album_images.album WHERE albums.id = $id GROUP BY albums.id;";
$result = mysqli_query ( $conn->db, $sql );
$r = mysqli_fetch_assoc ( $result );
$r ['date'] = substr ( $r ['date'], 0, 10 );
if ($r ['code'] == NULL) {
    $r ['code'] = "";
}
echo json_encode ( $r );

$conn->disconnect ();
exit ();