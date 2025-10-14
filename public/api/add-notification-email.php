<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $album = Album::withId($api->retrievePostString('album', 'Album id'));
    $email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

// update our mysql database
$sql = new Sql ();
$sql->executeStatement("INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('{$album->getId()}', '{$systemUser->getIdentifier()}', '$email');");
$sql->disconnect();
exit ();
