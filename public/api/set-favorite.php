<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$userId = $user->getIdentifier();

try {
    $album = new Album($_POST['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

try {
    $image = new Image($album, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

if ($user->isLoggedIn()) {
    // update our user records table
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Set Favorite', '{$image->getId()}', {$album->getId()} );");
}

// update our mysql database
$sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('$userId', '{$album->getId()}', '{$image->getId()}');");
// get our new favorite count for the album
echo $sql->getRow("SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '$userId' AND `album` = '{$album->getId()}';") ['total'];
$sql->disconnect();
exit ();
?>