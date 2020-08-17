<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

if ($systemUser->isAdmin()) {
    echo 1;
    $sql->disconnect();
    exit ();
}

try {
    $album = new Album($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

if (!$album->canUserAccess()) {
    echo 0;
    $sql->disconnect();
    exit();
}

if ($album->canUserGetData()) {
    echo 1;
    $sql->disconnect();
    exit();
}

try {
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

$downloadable = $sql->getRow("SELECT * FROM `download_rights` WHERE ( `user` = '{$systemUser->getIdentifier()}' OR `user` = '0' ) AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );");
if ($downloadable ['album']) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();