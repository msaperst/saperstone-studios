<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$username = $api->retrieveValidatedPost('username', 'Username', '/^[\w]{5,}$/');
if (is_array($username)) {
    echo $username['error'];
    exit();
}
//if (isset ($_POST ['username']) && preg_match('/^[\w]{5,}$/', $_POST ['username'])) {
//    $username = $sql->escapeString($_POST ['username']);
//} else {
//    echo "Your username must be at least 5 characters, and contain only letters numbers and underscores";
//    $conn->disconnect();
//    exit ();
//}
$row = $sql->getRow("SELECT * FROM users WHERE usr = '$username'");
if ($row ['usr']) {
    echo "That username already exists in the system";
    $sql->disconnect();
    exit ();
}

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
if (is_array($email)) {
    echo $email['error'];
    exit();
}
$row = $sql->getRow("SELECT email FROM users WHERE email='$email'");
if ($row ['email']) {
    echo "We already have an account on file for that email address, try resetting your password";  //TODO - make this a link!!
    $sql->disconnect();
    exit ();
}

$firstName = $lastName = "";
if (isset ($_POST ['firstName'])) {
    $firstName = $sql->escapeString($_POST ['firstName']);
}
if (isset ($_POST ['lastName'])) {
    $lastName = $sql->escapeString($_POST ['lastName']);
}

$password = $api->retrievePostString('password', 'Password');
if (is_array($password)) {
    echo $password['error'];
    exit();
} else {
    $password = md5( $password );
}

$hash = md5("$username-$password");
$lastId = $sql->executeStatement( "INSERT INTO `users` (`usr`, `pass`, `firstName`, `lastName`, `email`, `hash`) VALUES ('$username', '$password', '$firstName', '$lastName', '$email', '$hash');" );
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( $lastId, CURRENT_TIMESTAMP, 'Registered', NULL, NULL );");

$_SESSION ['usr'] = $username;
$_SESSION ['hash'] = $hash;
// Store some data in the session

$preferences = json_decode($_COOKIE['CookiePreferences']);
if ($_POST['rememberMe'] && in_array("preferences", $preferences)) {
    // remember the user if prompted
    $_COOKIE['hash'] = $hash;
    $_COOKIE ['usr'] = $username;
    setcookie('hash', $hash, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    setcookie('usr', $username, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
}

$sql->executeStatement("UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE hash='$hash';");
sleep(1);   //why are we sleeping?
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( $lastId, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );");
$sql->disconnect();
exit ();

//TODO - send email similar to create-user?