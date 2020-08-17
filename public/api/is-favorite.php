<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

try {
    $album = new Album($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
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

$favorite = $sql->getRow("SELECT * FROM `favorites` WHERE `user` = '{$systemUser->getIdentifier()}' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}';");
if ($favorite ['user']) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();