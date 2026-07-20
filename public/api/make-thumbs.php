<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrievePostString('id', 'Album id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

try {
    $markup = $api->retrievePostString('markup', 'Markup');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if ($markup != "proof" && $markup != "watermark" && $markup != "none") {
    echo "Markup is not valid";
    exit ();
}

if (!$systemUser->isAdmin()) {
    // update our user records table
    $sql = new Sql ();
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$systemUser->getId()}, CURRENT_TIMESTAMP, 'Created Thumbs', NULL, {$album->getId()} );");
    $sql->disconnect();
}

$scriptPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "bin/make-thumbs.sh";
$albumId = (int)$album->getId();
$escapedMarkup = escapeshellarg($markup);
$escapedLocation = escapeshellarg($album->getLocation());

system("$scriptPath $albumId $escapedMarkup $escapedLocation > /dev/null 2>&1 &");

exit ();
