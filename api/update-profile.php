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

if (! $user->isLoggedIn ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$firstName = "";
$lastName = "";
$curPass = "";
$email = "";

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

$sql = "UPDATE users SET firstName='$firstName', lastName='$lastName', email='$email' WHERE id='" . $user->getId () . "';";
mysqli_query ( $conn->db, $sql );

if (isset ( $_POST ['password'] ) && $_POST ['password'] != "" && isset ( $_POST ['curPass'] ) && $_POST ['curPass'] == "") {
    echo "Please confirm old password to set new password";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $curPass = mysqli_real_escape_string( $conn->db, $_POST['curPass'] );
    $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT usr FROM users WHERE id='" . $user->getId () . "' AND pass='$curPass'" ) );
    if (! $row ['usr']) {
        echo "That password does not match what we have on record for you";
        $conn->disconnect ();
        exit ();
    }
    $password = mysqli_real_escape_string ( $conn->db, $_POST ['password'] );
    $sql = "UPDATE users SET pass='$password' WHERE id='" . $user->getId () . "';";
    mysqli_query ( $conn->db, $sql );
}

$conn->disconnect ();
exit ();