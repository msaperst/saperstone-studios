<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

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

$err = array ();

if (isset ( $_POST ['username'] ) && $_POST ['username'] != "") {
    $username = $sql->escapeString( $_POST ['username'] );
} else {
    $err [] = "Username is not provided";
}
if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $email = $sql->escapeString( $_POST ['email'] );
    $row = $sql->getRow( "SELECT email FROM users WHERE email='$email'" );
    if ($row ['email']) {
        $err [] = "We already have an account on file for that email address.";
    }
} elseif ($_POST ['email'] == "") {
    $err [] = "Email is not provided!";
} else {
    $err [] = "Enter a valid email address!";
}

$sql = "SELECT * FROM users WHERE usr = '$username'";
$row = $sql->getRow( $sql );
if ($row ['usr']) {
    $err [] = "That user ID already exists";
}

if (count ( $err ) > 0) {
    echo implode ( '<br />', $err );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['firstName'] )) {
    $firstName = $sql->escapeString( $_POST ['firstName'] );
}
if (isset ( $_POST ['lastName'] )) {
    $lastName = $sql->escapeString( $_POST ['lastName'] );
}
if (isset ( $_POST ['role'] )) {
    $role = $sql->escapeString( $_POST ['role'] );
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