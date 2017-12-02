<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
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
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$markup = "";
if (isset ( $_POST ['markup'] )) {
    $markup = mysqli_real_escape_string ( $conn->db, $_POST ['markup'] );
} else {
    echo "Markup is required!";
    $conn->disconnect ();
    exit ();
}
if ($markup != "proof" && $markup != "watermark" && $markup != "none") {
    echo "Markup is not a valid option!";
    $conn->disconnect ();
    exit ();
}

if (! $user->isAdmin ()) {
    // update our user records table
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Thumbs', NULL, $id );" );
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$result = mysqli_query ( $conn->db, $sql );
$album_info = mysqli_fetch_assoc ( $result );

system ( dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "bin/make-thumbs.sh $id $markup " . $album_info ['location'] . " > /dev/null 2>&1 &" );

$conn->disconnect ();
exit ();