<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $album = Album::withId($_POST['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
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