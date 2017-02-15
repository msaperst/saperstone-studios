<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

$id = "";
$username = "";
$firstName = "";
$lastName = "";
$email = "";

if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    echo "There was some error, please try again";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['username'] ) && preg_match ( '/^[\w]{5,}$/', $_POST ['username'] )) {
    $username = mysqli_real_escape_string ( $conn->db, $_POST ['username'] );
} else {
    echo "Your username must be at least 5 characters, and contain only letters numbers and underscores";
    $conn->disconnect ();
    exit ();
}

$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT usr FROM users WHERE usr='$username'" ) );
if ($row ['usr']) {
    echo "That username is not available, please try a different one";
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

if (isset ( $_POST ['firstName'] ) && $_POST ['firstName'] != "") {
    $firstName = mysqli_real_escape_string ( $conn->db, $_POST ['firstName'] );
} else {
    echo "First name is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['lastName'] ) && $_POST ['lastName'] != "") {
    $lastName = mysqli_real_escape_string ( $conn->db, $_POST ['lastName'] );
} else {
    echo "Last name is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $password = mysqli_real_escape_string ( $conn->db, $_POST ['password'] );
} else {
    echo "Password is not provided";
    $conn->disconnect ();
    exit ();
}

$hash = md5 ( "$username-$password" );
$sql = "INSERT INTO `users` (`id`, `usr`, `pass`, `firstName`, `lastName`, `email`, `hash`) VALUES ($id, '$username', '$password', '$firstName', '$lastName', '$email', '$hash');";
mysqli_query ( $conn->db, $sql );
$sql = "DELETE FROM `old_users` WHERE `id` = $id;";
mysqli_query ( $conn->db, $sql );
mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( $id, CURRENT_TIMESTAMP, 'Converted User', NULL, NULL );" );


// need to auto-login
session_name ( 'ssLogin' );
session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
session_start ();

$_SESSION ['usr'] = $username;
$_SESSION ['hash'] = $hash;
$_SESSION ['rememberMe'] = 1;

setcookie ( 'ssRemember', 1 );
// We create the tzRemember cookie

mysqli_query ( $conn->db, "UPDATE users SET lastLogin=CURRENT_TIMESTAMP WHERE hash='$hash';" );
sleep ( 1 );
mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( $id, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );" );


$conn->disconnect ();
exit ();