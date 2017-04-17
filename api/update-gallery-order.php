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

// only admin users can make updates
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = ( int ) $_POST ['id'];
} else {
    if (! isset ( $_POST ['id'] )) {
        echo "Gallery ID is required!";
    } elseif ($_POST ['id'] != "") {
        echo "Gallery ID cannot be blank!";
    } else {
        echo "Some other Gallery ID error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM galleries WHERE id = $id;";
$gallery_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $gallery_info ['id']) {
    echo "That ID doesn't match any galleries";
    $conn->disconnect ();
    exit ();
}

$imgs;

if (isset ( $_POST ['imgs'] ) && is_array ( $_POST ['imgs'] )) {
    $imgs = $_POST ['imgs'];
} else {
    echo "The images you passed in are in an invalid format";
    $conn->disconnect ();
    exit ();
}

for($x = 0; $x < sizeof ( $imgs ); $x ++) {
    $img = $imgs [$x];
    $sql = "UPDATE gallery_images SET sequence=" . $x . " WHERE id='" . ( int ) $img ['id'] . "';";
    mysqli_query ( $conn->db, $sql );
}

$conn->disconnect ();
exit ();