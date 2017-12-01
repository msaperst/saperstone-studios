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

$user_id;
if (! $user->isLoggedIn ()) {
    $user_id = $_SERVER ['REMOTE_ADDR'];
} else {
    $user_id = $user->getId ();
}

$album = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['album'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

$email = "";
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
} else {
    if (! isset ( $_POST ['email'] )) {
        echo "Email is required!";
    } elseif ($_POST ['email'] != "") {
        echo "Email cannot be blank!";
    } else {
        echo "Some other email error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

// update our mysql database
$sql = "INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('$album', '$user_id', '$email');";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();