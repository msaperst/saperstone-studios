<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $code = $api->retrieveGetString('code', 'Album code');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$r = $sql->getRow("SELECT * FROM albums WHERE code = ?", [$code]);
if (isset($r ['id'])) {
    $_SESSION ["searched"] [$r ['id']] = md5("album" . $code);
    echo $r ['id'];
} else {
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
