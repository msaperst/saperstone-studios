<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $album = new Album($_GET['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($album, $_GET['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$favorite = $sql->getRow("SELECT user FROM `favorites` WHERE `user` = '{$systemUser->getIdentifier()}' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}';");
if ($favorite ['user']) {
    echo 1;
} else {
    echo 0;
}
$sql->disconnect();
exit ();