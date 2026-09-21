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
session_set_cookie_params(CookieManager::sessionOptions(60 * 60));
session_start();
session_regenerate_id(true);

$_SESSION ['usr'] = $user->getUsername();
$_SESSION ['hash'] = $user->getHash();
RememberMe::clearLegacyCookies();
exit ();
