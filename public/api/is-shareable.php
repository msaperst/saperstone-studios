<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

if ($systemUser->isAdmin()) {
    echo 1;
    exit ();
}

try {
    $album = Album::withId($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

if (!$album->canUserAccess()) {
    echo 0;
    exit();
}

if ($album->canUserGetData()) {
    echo 1;
    exit();
}

try {
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$shareable = $sql->getRow("SELECT album FROM `share_rights` WHERE ( `user` = '{$systemUser->getIdentifier()}' OR `user` = '0' ) AND ( `album` = '{$album->getId()}' OR `album` = '*' ) AND ( `image` = '{$image->getId()}' OR `image` = '*' );");
if ($shareable ['album']) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();