<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$userId = $systemUser->getIdentifier();

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

$sql = new Sql ();
if ($systemUser->isLoggedIn()) {
    // update our user records table
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( $userId, CURRENT_TIMESTAMP, 'Unset Favorite', '{$image->getId()}', {$album->getId()} );");
}

// update our mysql database
$sql->executeStatement("DELETE FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}' AND `image` = '{$image->getId()}';");
// get our new favorite count for the album
echo $sql->getRowCount("SELECT * FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}';");
$sql->disconnect();
exit ();