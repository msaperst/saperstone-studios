<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$username = "";
$firstName = "";
$lastName = "";
$email = "";

if (isset ( $_POST ['username'] ) && preg_match ( '/^[\w]{5,}$/', $_POST ['username'] )) {
    $username = $sql->escapeString( $_POST ['username'] );
} else {
    echo "Your username must be at least 5 characters, and contain only letters numbers and underscores";
    $conn->disconnect ();
    exit ();
}

$row = $sql->getRow( "SELECT usr FROM users WHERE usr='$username'" );
if ($row ['usr']) {
    echo "That username is not available, please try a different one";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    $email = $sql->escapeString( $_POST ['email'] );
} else {
    echo "Email is not provided";
    $conn->disconnect ();
    exit ();
}

$row = $sql->getRow( "SELECT email FROM users WHERE email='$email'" );
if ($row ['email']) {
    echo "We already have an account on file for that email address. Try resetting your password.";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['firstName'] ) && $_POST ['firstName'] != "") {
    $firstName = $sql->escapeString( $_POST ['firstName'] );
} else {
    echo "First name is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['lastName'] ) && $_POST ['lastName'] != "") {
    $lastName = $sql->escapeString( $_POST ['lastName'] );
} else {
    echo "Last name is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['password'] ) && $_POST ['password'] != "") {
    $password = md5( $sql->escapeString( $_POST ['password'] ) );
} else {
    echo "Password is not provided";
    $conn->disconnect ();
    exit ();
}

$hash = md5 ( "$username-$password" );
$sql = "INSERT INTO `users` (`usr`, `pass`, `firstName`, `lastName`, `email`, `hash`) VALUES ('$username', '$password', '$firstName', '$lastName', '$email', '$hash');";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( $last_id, CURRENT_TIMESTAMP, 'Registered', NULL, NULL );" );

$_SESSION ['usr'] = $username;
$_SESSION ['hash'] = $hash;
 // Store some data in the session

$preferences = json_decode( $_COOKIE['CookiePreferences'] );
if( $_POST['rememberMe'] && in_array( "preferences", $preferences ) ) {
    // remember the user if prompted
    $_COOKIE['hash'] = $hash;
    $_COOKIE ['usr'] = $username;
    setcookie ( 'hash', $hash, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    setcookie ( 'usr', $username, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
}

mysqli_query ( $conn->db, "UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE hash='$hash';" );
sleep ( 1 );
mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( $last_id, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );" );

$conn->disconnect ();
exit ();