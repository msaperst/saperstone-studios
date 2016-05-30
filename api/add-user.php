<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

$username = "";
$firstName = "";
$lastName = "";
$email = "";
$role = "";
$active = "";

if( isset( $_POST['username'] ) && $_POST['username'] != "" ) {
    $username = mysqli_real_escape_string ( $db, $_POST ['username'] );
} else {
    echo "Username is not provided";
    exit();
}
if( isset( $_POST['email'] ) && $_POST['email'] != "" ) {
    $email = mysqli_real_escape_string ( $db, $_POST ['email'] );
} else {
    echo "Email is not provided";
    exit();
}

$sql = "SELECT * FROM users WHERE usr = '$username'";
$row = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if( $row['usr'] ) {
    echo "That user ID already exists";
    exit();
}

if( isset( $_POST['firstName'] ) ) {
    $firstName = mysqli_real_escape_string ( $db, $_POST ['firstName'] );
}
if( isset( $_POST['lastName'] ) ) {
    $lastName = mysqli_real_escape_string ( $db, $_POST ['lastName'] );
}
if( isset( $_POST['role'] ) ) {
    $role = mysqli_real_escape_string ( $db, $_POST ['role'] );
}
if( isset( $_POST['active'] ) ) {
    $active = $_POST ['active'];
}
$sql = "INSERT INTO users ( usr, firstName, lastName, email, role, active, hash ) VALUES ('$username', '$firstName', '$lastName', '$email', '$role', '$active', '" . md5( $username . $role ) . "' );";
mysqli_query ( $db, $sql );
exit ();