<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrievePostString('id', 'Album id'));
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

try {
    $requestedMarkup = $api->retrievePostString('markup', 'Markup');
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

if ($requestedMarkup === "proof") {
    $markup = "proof";
} elseif ($requestedMarkup === "watermark") {
    $markup = "watermark";
} elseif ($requestedMarkup === "none") {
    $markup = "none";
} else {
    http_response_code(400);
    echo "Markup is not valid";
    exit ();
}

$requestedMode = $_POST['mode'] ?? 'missing';
if ($requestedMode === "missing") {
    $mode = "missing";
} elseif ($requestedMode === "all") {
    $mode = "all";
} else {
    http_response_code(400);
    echo "Thumbnail mode is not valid";
    exit ();
}

if (!$systemUser->isAdmin()) {
    // update our user records table
    $sql = new Sql ();
    $sql->executeStatement("INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES (?, CURRENT_TIMESTAMP, 'Created Thumbs', NULL, ?)", [$systemUser->getId(), $album->getId()]);
    $sql->disconnect();
}

$scriptPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "bin/make-thumbs.sh";
$albumId = (int)$album->getId();

system("$scriptPath $albumId $markup $mode > /dev/null 2>&1 &");

exit ();
