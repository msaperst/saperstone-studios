<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
Api::requireMethod('POST');
$api = new Api ();

$api->forceAdmin();

try {
    $album = Album::withId($api->retrievePostString('album', 'Album id'));
    $image = $api->retrievePostString('image', 'Image id');
    if ($image != '*') {
        $image = (new Image($album, $image)->getId());
    }
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$sql->executeStatement("DELETE FROM `download_rights` WHERE `album` = ? AND `image` = ?", [$album->getId(), $image]);

if (isset ($_POST ['users']) && is_array($_POST ['users'])) {
    foreach ($_POST ['users'] as $user) {
        $sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES (?, ?, ?)", [$user, $album->getId(), $image]);
    }
}
$sql->disconnect();
exit ();
