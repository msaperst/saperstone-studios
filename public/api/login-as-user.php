<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($api->retrievePostString('id', 'User id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

session_unset();
session_destroy();

session_name('session');
// Making the cookie live for 1 hour
session_set_cookie_params(60 * 60);
session_start();

$_SESSION ['usr'] = $user->getUsername();
$_SESSION ['hash'] = $user->getHash();
setcookie('hash', '', time() - 3600, '/');
setcookie('usr', '', time() - 3600, '/');
exit ();
