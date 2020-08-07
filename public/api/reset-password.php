<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
$sql = new Sql ();

$err = array ();

if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $_POST ['email'] = $sql->escapeString( $_POST ['email'] );
} elseif ($_POST ['email'] == "") {
    $err [] = "All the fields must be filled in!";
} else {
    $err [] = "Enter a valid email address!";
}
if (isset ( $_POST ['code'] ) && $_POST ['code'] != "") {
    $_POST ['code'] = $sql->escapeString( $_POST ['code'] );
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $_POST ['password'] = $sql->escapeString( $_POST ['password'] );
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ( $_POST ['passwordConfirm'] ) && $_POST ['passwordConfirm'] != "") {
    $_POST ['passwordConfirm'] = $sql->escapeString( $_POST ['passwordConfirm'] );
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

$row = $sql->getRow( "SELECT * FROM users WHERE email='{$_POST['email']}' AND resetKey='{$_POST ['code']}';" );
if ($row ['usr']) {
    // If everything is OK login, so update our password
    mysqli_query ( $conn->db, "UPDATE users SET pass='" . md5 ( $_POST ['password'] ) . "' WHERE email='{$_POST ['email']}' AND resetKey='{$_POST ['code']}';" );
    mysqli_query ( $conn->db, "UPDATE users SET resetKey=NULL WHERE email='{$_POST ['email']}';" );
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$row ['id']}, CURRENT_TIMESTAMP, 'Reset Password', NULL, NULL );" );
    
    // If everything is OK login
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
    $_SESSION ['usr'] = $row ['usr'];
    $_SESSION ['hash'] = $row ['hash'];
     // Store some data in the session

    $preferences = json_decode( $_COOKIE['CookiePreferences'] );
    if( $_POST['rememberMe'] && in_array( "preferences", $preferences ) ) {
        // remember the user if prompted
        $_COOKIE['hash'] = $row ['hash'];
        $_COOKIE ['usr'] = $row ['usr'];
        setcookie ( 'hash', $row ['hash'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
        setcookie ( 'usr', $row ['usr'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    }
    
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