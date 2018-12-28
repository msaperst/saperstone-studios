<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

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

$name = "";
$description = "";
$date = "NULL";
$code = "";

if (isset ( $_POST ['name'] )) {
    $name = mysqli_real_escape_string ( $conn->db, $_POST ['name'] );
}
if (isset ( $_POST ['description'] )) {
    $description = mysqli_real_escape_string ( $conn->db, $_POST ['description'] );
}
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['date'] ) . "'";
}
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "") {
    $code = mysqli_real_escape_string ( $conn->db, $_POST ['code'] );
}

$sql = "UPDATE albums SET name='$name', description='$description', date=$date, code=NULL WHERE id='$id';";
mysqli_query ( $conn->db, $sql );
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "" && $user->isAdmin ()) {
    $code = mysqli_real_escape_string ( $conn->db, $_POST ['code'] );
    $codeExist = mysqli_num_rows ( mysqli_query ( $conn->db, "SELECT * FROM `albums` WHERE code = '$code';" ) );
    if (! $codeExist) {
        $sql = "UPDATE albums SET code='$code' WHERE id='$id';";
        mysqli_query ( $conn->db, $sql );
    } else {
        echo "That Album Code already exists.";
        $conn->disconnect ();
        exit ();
    }
}
$conn->disconnect ();
exit ();
