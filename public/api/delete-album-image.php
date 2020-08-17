<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);
$sql->disconnect();

$api->forceAdmin();

try {
    $album = new Album($_POST['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($album, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$image->delete();
exit ();