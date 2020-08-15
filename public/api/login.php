<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser($sql);
$api = new Api($sql, $user);

if ($user->isLoggedIn() && $_POST ['submit'] == 'Logout') {
    // note the logout
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Logged Out', NULL, NULL );");
    $sql->disconnect();

    // remove any stored login
    unset($_COOKIE['hash']);
    unset($_COOKIE['usr']);
    setcookie('hash', null, -1, '/');
    setcookie('usr', null, -1, '/');

    // destroy the session
    session_unset();
    session_destroy();
    exit ();
}

if ($_POST ['submit'] == 'Login') {

    $username = $api->retrievePostString('username', 'Username');
    if (is_array($username)) {
        echo $username['error'];
        $sql->disconnect();
        exit();
    }
    $password = $api->retrievePostString('password', 'Password');
    if (is_array($password)) {
        echo $password['error'];
        $sql->disconnect();
        exit();
    } else {
        $password = md5($password);
    }
    $remember = ( int )$_POST ['rememberMe'];

    $row = $sql->getRow("SELECT * FROM users WHERE usr='$username' AND pass='$password'");
    if ($row ['usr'] && $row ['active']) {  // If everything is OK login

        $_SESSION ['usr'] = $row ['usr'];   // store our login data
        $_SESSION ['hash'] = $row ['hash'];

        $preferences = json_decode($_COOKIE['CookiePreferences']);
        if ($remember && is_array($preferences) && in_array("preferences", $preferences)) {    //if we should be remembered, and cookies are allowed
            $_COOKIE ['hash'] = $row ['hash'];
            $_COOKIE ['usr'] = $row ['usr'];
            setcookie('hash', $row ['hash'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
            setcookie('usr', $row ['usr'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
        }
        $user = new CurrentUser($sql);
        $sql->executeStatement("UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE id='{$user->getId()}';");
        $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );");
    } elseif ($row ['usr']) {
        echo 'Sorry, you account has been deactivated. Please <a target="_blank" href="mailto:webmaster@saperstonestudios.com">contact our webmaster</a> to get this resolved.';
        $sql->disconnect();
        exit();
    } else {
        echo 'Credentials do not match our records';
        $sql->disconnect();
        exit();
    }
}

$sql->disconnect();
exit ();