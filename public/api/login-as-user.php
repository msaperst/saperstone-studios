<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

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
        echo "User id is required!";
    } elseif ($_POST ['id'] != "") {
        echo "User id cannot be blank!";
    } else {
        echo "Some other User id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM users WHERE id = $id;";
$user_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $user_info ['id']) {
    echo "That ID doesn't match any users";
    $conn->disconnect ();
    exit ();
}

session_unset ();
session_destroy ();

session_name ( 'ssLogin' );
// Making the cookie live for 1 hour
session_set_cookie_params ( 60 * 60 );
session_start ();

$_SESSION ['usr'] = $user_info ['usr'];
$_SESSION ['hash'] = $user_info ['hash'];
$_SESSION ['rememberMe'] = 0;
setcookie ( 'ssRemember', 0 );

$conn->disconnect ();
exit ();