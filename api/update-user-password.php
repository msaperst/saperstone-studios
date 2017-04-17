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

$err = array ();

if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    $err [] = "ID is not provided";
}
if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $_POST ['password'] = mysqli_real_escape_string ( $conn->db, $_POST ['password'] );
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ( $_POST ['passwordConfirm'] ) && $_POST ['passwordConfirm'] != "") {
    $_POST ['passwordConfirm'] = mysqli_real_escape_string ( $conn->db, $_POST ['passwordConfirm'] );
} else {
    $err [] = "All the fields must be filled in!";
}

if ($_POST ['password'] != $_POST ['passwordConfirm']) {
    $err [] = "Password and Confirmation do not match!";
}

if ($_POST ['password'] == "" || $_POST ['passwordConfirm'] == "") {
    $err [] = "Password cannot be blank";
}
$err = array_unique ( $err );

if (count ( $err ) > 0) {
    echo implode ( '<br />', $err );
    $conn->disconnect ();
    exit ();
}

mysqli_query ( $conn->db, "UPDATE users SET pass='" . $_POST ['password'] . "' WHERE id='$id';" );

$conn->disconnect ();
exit ();