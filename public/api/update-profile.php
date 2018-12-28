<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

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
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT email FROM users WHERE email='$email' AND id!={$user->getId()}" ) );
if ($row ['email']) {
    echo "We already have another account on file for that email address. Try a different email address, or use the account associated with that email.";
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
    $curPass = mysqli_real_escape_string ( $conn->db, $_POST ['curPass'] );
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
mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId ()}, CURRENT_TIMESTAMP, 'Updated User', NULL, NULL );" );

$conn->disconnect ();
exit ();