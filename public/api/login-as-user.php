<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withId($_POST['id']);
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
setcookie('hash', null, -1, '/');
setcookie('usr', null, -1, '/');
exit ();