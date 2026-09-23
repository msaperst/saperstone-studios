<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}

$api = new Api();
$api->forceLoggedIn();
$systemUser = User::fromSystem();

try {
    $code = $api->retrievePostString('code', 'Album code');
    $album = Album::withCode($code);
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$existing = $sql->getRow(
    "SELECT user FROM albums_for_users WHERE user = ? AND album = ?",
    [$systemUser->getId(), $album->getId()]
);
if (!isset($existing['user'])) {
    $sql->executeStatement(
        "INSERT INTO albums_for_users (`user`, `album`) VALUES (?, ?)",
        [$systemUser->getId(), $album->getId()]
    );
}
$sql->disconnect();

echo $album->getId();
exit();
