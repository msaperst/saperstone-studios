<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

try {
    $album = new Album($_POST['id']);
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

system(dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "bin/make-thumbs.sh {$album->getId()} $markup {$album->getLocation()} > /dev/null 2>&1 &");
exit ();