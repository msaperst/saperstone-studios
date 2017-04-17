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

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$username = "";
$firstName = "";
$lastName = "";
$email = "";
$role = "";
$active = "";

if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}
if (isset ( $_POST ['username'] ) && $_POST ['username'] != "") {
    $username = mysqli_real_escape_string ( $conn->db, $_POST ['username'] );
} else {
    echo "Username is not provided";
    $conn->disconnect ();
    exit ();
}
if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
} else {
    echo "Email is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM users WHERE usr = '$username' AND id != '$id';";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($row ['usr']) {
    echo "That user ID already exists";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['firstName'] )) {
    $firstName = mysqli_real_escape_string ( $conn->db, $_POST ['firstName'] );
}
if (isset ( $_POST ['lastName'] )) {
    $lastName = mysqli_real_escape_string ( $conn->db, $_POST ['lastName'] );
}
if (isset ( $_POST ['role'] )) {
    $role = mysqli_real_escape_string ( $conn->db, $_POST ['role'] );
}
if (isset ( $_POST ['active'] )) {
    $active = ( int ) $_POST ['active'];
}

$sql = "UPDATE users SET usr='$username', firstName='$firstName', lastName='$lastName', email='$email', role='$role', active='$active' WHERE id='$id';";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();