<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

try {
    $code = $api->retrieveGetString('code', 'Album code');
    $album = Album::withCode($code);
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$_SESSION["searched"][$album->getId()] = hash('sha256', "album" . $code);
if (isset($_COOKIE['CookiePreferences'])) {
    $preferences = json_decode($_COOKIE['CookiePreferences'], true);
    if (is_array($preferences) && in_array('preferences', $preferences, true)) {
        $searched = isset($_COOKIE['searched']) ? json_decode($_COOKIE['searched'], true) : [];
        if (!is_array($searched)) {
            $searched = [];
        }
        $searched[$album->getId()] = hash('sha256', "album" . $code);
        CookieManager::set('searched', json_encode($searched), time() + 30 * 24 * 60 * 60);
    }
}

echo $album->getId();
exit();
