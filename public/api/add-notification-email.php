<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$userId = $systemUser->getIdentifier();

try {
    $album = new Album($_POST['album']);
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
if (is_array($email)) {
    echo $email['error'];
    exit();
}

// update our mysql database
$sql->executeStatement("INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('{$album->getId()}', '$userId', '$email');");
$sql->disconnect();
exit ();