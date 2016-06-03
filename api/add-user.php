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
    header ( 'HTTP/1.0 401 Unauthorized' );
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
    $username = mysqli_real_escape_string ( $db, $_POST ['username'] );
} else {
    $err [] = "Username is not provided";
}
if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $email = mysqli_real_escape_string ( $db, $_POST ['email'] );
} elseif ($_POST ['email'] == "") {
    $err [] = "Email is not provided!";
} else {
    $err [] = "Enter a valid email address!";
}

$sql = "SELECT * FROM users WHERE usr = '$username'";
$row = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if ($row ['usr']) {
    $err [] = "That user ID already exists";
}

if (count ( $err ) > 0) {
    echo implode ( '<br />', $err );
    exit ();
}

if (isset ( $_POST ['firstName'] )) {
    $firstName = mysqli_real_escape_string ( $db, $_POST ['firstName'] );
}
if (isset ( $_POST ['lastName'] )) {
    $lastName = mysqli_real_escape_string ( $db, $_POST ['lastName'] );
}
if (isset ( $_POST ['role'] )) {
    $role = mysqli_real_escape_string ( $db, $_POST ['role'] );
}
if (isset ( $_POST ['active'] )) {
    $active = $_POST ['active'];
}
$sql = "INSERT INTO users ( usr, firstName, lastName, email, role, active, hash ) VALUES ('$username', '$firstName', '$lastName', '$email', '$role', '$active', '" . md5 ( $username . $role ) . "' );";
mysqli_query ( $db, $sql );
$last_id = mysqli_insert_id ( $db );

echo $last_id;

exit ();