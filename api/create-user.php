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
    $conn->disconnect ();
    exit ();
}

$username = "";
$firstName = "";
$lastName = "";
$email = "";
$role = "";
$active = "";

$err = array ();

if (isset ( $_POST ['username'] ) && $_POST ['username'] != "") {
    $username = mysqli_real_escape_string ( $conn->db, $_POST ['username'] );
} else {
    $err [] = "Username is not provided";
}
if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
    $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT email FROM users WHERE email='$email'" ) );
    if ($row ['email']) {
        $err [] = "We already have an account on file for that email address.";
    }
} elseif ($_POST ['email'] == "") {
    $err [] = "Email is not provided!";
} else {
    $err [] = "Enter a valid email address!";
}

$sql = "SELECT * FROM users WHERE usr = '$username'";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($row ['usr']) {
    $err [] = "That user ID already exists";
}

if (count ( $err ) > 0) {
    echo implode ( '<br />', $err );
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
$sql = "INSERT INTO users ( usr, firstName, lastName, email, role, active, hash ) VALUES ('$username', '$firstName', '$lastName', '$email', '$role', '$active', '" . md5 ( $username . $role ) . "' );";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

echo $last_id;

$conn->disconnect ();
exit ();