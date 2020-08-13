<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);
$sql->disconnect();

$api->forceLoggedIn();

try {
    $album = new Album($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

$albumInfo = $album->getDataArray();
$albumInfo ['date'] = substr($albumInfo ['date'], 0, 10);
if ($albumInfo ['code'] == NULL) {
    $albumInfo ['code'] = "";
}
echo json_encode($albumInfo);
exit ();