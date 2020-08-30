<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$session = new Session();
$session->initialize();
$api = new Api();

if ($systemUser->isLoggedIn() && $_POST ['submit'] == 'Logout') {
    $sql = new Sql ();
    // note the logout
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$systemUser->getId()}, CURRENT_TIMESTAMP, 'Logged Out', NULL, NULL );");
    $sql->disconnect();

    // remove any stored login
    setcookie('hash', null, -1, '/');
    setcookie('usr', null, -1, '/');

    // destroy the session
    session_unset();
    session_destroy();
    exit ();
}

if ($_POST ['submit'] == 'Login') {
    try {
        $username = $api->retrievePostString('username', 'Username');
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }

    try {
        $password = $api->retrievePostString('password', 'Password');
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }

    try {
        $user = User::fromLogin($username, $password);
    } catch (Exception $e) {
        echo "Credentials do not match our records";
        exit();
    }

    if (!$user->isActive()) {
        echo 'Sorry, you account has been deactivated. Please <a target="_blank" href="mailto:webmaster@saperstonestudios.com">contact our webmaster</a> to get this resolved.';
        exit();
    }
    $user->login(boolval((int)$_POST ['rememberMe']));
}

exit ();