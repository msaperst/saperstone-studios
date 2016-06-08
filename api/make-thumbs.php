<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

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
if (getRole () == "admin" || (getRole () == "uploader" && getUserId () == $album_info ['owner'])) {
} else {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$result = mysqli_query ( $db, $sql );
$album_info = mysqli_fetch_assoc ( $result );

system ( "../scripts/make-thumbs.sh $id " . $album_info ['location'] . " > /dev/null 2>&1 &" );