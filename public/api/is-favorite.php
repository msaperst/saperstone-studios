<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

try {
    $album = Album::withId($_GET['album']);
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