<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

$err = array ();

if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $_POST ['email'] = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
} elseif ($_POST ['email'] == "") {
    $err [] = "All the fields must be filled in!";
} else {
    $err [] = "Enter a valid email address!";
}
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "") {
    $_POST ['code'] = mysqli_real_escape_string ( $conn->db, $_POST ['code'] );
} else {
    $err [] = "All the fields must be filled in!";
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
$err = array_unique ( $err );

if (count ( $err ) > 0) {
    echo implode ( '<br />', $err );
    $conn->disconnect ();
    exit ();
}

$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT * FROM users WHERE email='{$_POST['email']}' AND resetKey='{$_POST ['code']}';" ) );
if ($row ['usr']) {
    // If everything is OK login, so update our password
    mysqli_query ( $conn->db, "UPDATE users SET pass='" . md5 ( $_POST ['password'] ) . "' WHERE email='{$_POST ['email']}' AND resetKey='{$_POST ['code']}';" );
    mysqli_query ( $conn->db, "UPDATE users SET resetKey=NULL WHERE email='{$_POST ['email']}';" );
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$row ['id']}, CURRENT_TIMESTAMP, 'Reset Password', NULL, NULL );" );
    
    // If everything is OK login
    session_name ( 'ssLogin' );
    session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
    session_start ();
    $_SESSION ['usr'] = $row ['usr'];
    $_SESSION ['hash'] = $row ['hash'];
    $_SESSION ['rememberMe'] = 1;
    
    setcookie ( 'ssRemember', 1 );
    
    mysqli_query ( $conn->db, "UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE hash='{$row ['hash']}';" );
    sleep ( 1 );
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$row ['id']}, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );" );
} else {
    $err [] = "Credentials do not match our records!";
}

if ($err) {
    // Save the error messages for the user
    echo implode ( '<br />', $err );
}
$conn->disconnect ();
exit ();