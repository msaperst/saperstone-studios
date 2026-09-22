<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $code = $api->retrieveGetString('code', 'Album code');
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$r = $sql->getRow("SELECT * FROM albums WHERE BINARY code = BINARY ?", [$code]);
if (isset($r ['id'])) {
    $_SESSION ["searched"] [$r ['id']] = hash('sha256', "album" . $code);
    if (isset($_COOKIE['CookiePreferences'])) {
        $preferences = json_decode($_COOKIE['CookiePreferences'], true);
        if (is_array($preferences) && in_array('preferences', $preferences, true)) {
            $searched = isset($_COOKIE['searched']) ? json_decode($_COOKIE['searched'], true) : [];
            if (!is_array($searched)) {
                $searched = [];
            }
            $searched[$r['id']] = hash('sha256', "album" . $code);
            CookieManager::set('searched', json_encode($searched), time() + 30 * 24 * 60 * 60);
        }
    }
    echo $r ['id'];
} else {
    http_response_code(400);
    echo "That code does not match any albums";
    $sql->disconnect();
    exit ();
}

if ($systemUser->isLoggedIn() && isset ($_GET ['albumAdd']) && $_GET ['albumAdd'] == 1) {
    $s = $sql->getRow("SELECT * FROM albums_for_users WHERE user = ? AND album = ?", [$systemUser->getId(), $r['id']]);
    if (!isset($s ['user'])) {
        $sql->executeStatement("INSERT INTO albums_for_users (`user`, `album`) VALUES (?, ?)", [$systemUser->getId(), $r['id']]);
    }
}

$sql->disconnect();
exit ();
