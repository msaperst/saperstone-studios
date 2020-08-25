<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $code = $api->retrieveGetString('code', 'Album code');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$r = $sql->getRow("SELECT * FROM albums WHERE code = '$code';");
if ($r ['id']) {
    $_SESSION ["searched"] [$r ['id']] = md5("album" . $code);
    $preferences = json_decode($_COOKIE['CookiePreferences']);
    if (is_array($preferences) && in_array("preferences", $preferences)) {
        $searched = array();
        if (isset($_COOKIE ["searched"])) {
            $searched = json_decode($_COOKIE ["searched"], true);
        }
        $searched [$r ['id']] = md5("album" . $code);
        setcookie('searched', json_encode($searched), time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    }
    echo $r ['id'];
} else {
    echo "That code does not match any albums";
    $sql->disconnect();
    exit ();
}

if ($systemUser->isLoggedIn() && isset ($_GET ['albumAdd']) && $_GET ['albumAdd'] == 1) {
    $s = $sql->getRow("SELECT * FROM albums_for_users WHERE user = '" . $systemUser->getId() . "' AND album = '" . $r ['id'] . "';");
    if (!$s ['user']) {
        $sql->executeStatement("INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '" . $systemUser->getId() . "', '" . $r ['id'] . "' );");
    }
}

$sql->disconnect();
exit ();
