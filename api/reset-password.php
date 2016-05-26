<?php
require_once "../php/sql.php";

$err = array ();

if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $_POST ['email'] = mysqli_real_escape_string ( $db, $_POST ['email'] );
} elseif( $_POST ['email'] == "" ) {
    $err [] = "All the fields must be filled in!";
} else {
    $err [] = "Enter a valid email address!";
}
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "") {
    $_POST ['code'] = mysqli_real_escape_string ( $db, $_POST ['code'] );
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $_POST ['password'] = mysqli_real_escape_string ( $db, $_POST ['password'] );
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ( $_POST ['passwordConfirm'] ) && $_POST ['passwordConfirm'] != "") {
    $_POST ['passwordConfirm'] = mysqli_real_escape_string ( $db, $_POST ['passwordConfirm'] );
} else {
    $err [] = "All the fields must be filled in!";
}

if ($_POST ['password'] != $_POST ['passwordConfirm']) {
    $err [] = "Password and Confirmation do not match!";
}
$err = array_unique( $err );

if( count( $err ) > 0 ) {
    echo implode ( '<br />', $err );
    exit();
}


$row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT usr FROM users WHERE email='{$_POST['email']}' AND resetKey='{$_POST ['code']}';" ) );
if ($row ['usr']) {
    // If everything is OK login, so update our password
    mysqli_query ( $db, "UPDATE users SET pass='" . md5 ( $_POST ['password'] ) . "' WHERE email='{$_POST ['email']}' AND resetKey='{$_POST ['code']}';" );
} else {
    $err [] = "Credentials do not match our records!";
}

if ($err) {
    // Save the error messages for the user
    echo implode ( '<br />', $err );
}
exit ();

?>