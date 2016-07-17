<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php"; $user = new user();

$id = "";
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = ( int ) $_POST ['id'];
} else {
    if (! isset ( $_POST ['id'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['id'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($album_info ['id']) {
} else {
    echo "That ID doesn't match any albums";
    exit ();
}
// only admin users and uploader users who own the album can make updates
if ($user->getRole () == "admin" || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner'])) {
} else {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

$name = "";
$description = "";
$date = "";
$code = "";

if (isset ( $_POST ['name'] )) {
    $name = mysqli_real_escape_string ( $db, $_POST ['name'] );
}
if (isset ( $_POST ['description'] )) {
    $description = mysqli_real_escape_string ( $db, $_POST ['description'] );
}
if (isset ( $_POST ['date'] )) {
    $date = mysqli_real_escape_string ( $db, $_POST ['date'] );
}
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "") {
    $code = mysqli_real_escape_string ( $db, $_POST ['code'] );
}

$sql = "UPDATE albums SET name='$name', description='$description', date='$date', code=NULL WHERE id='$id';";
mysqli_query ( $db, $sql );
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "" && $user->getRole () == "admin") {
    $code = mysqli_real_escape_string ( $db, $_POST ['code'] );
    $sql = "UPDATE albums SET code='$code' WHERE id='$id';";
    mysqli_query ( $db, $sql );
}
exit ();

?>