<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();
$api->forceAdmin();

try {
    $album = Album::withId($_POST['album']);
    $message = $api->retrievePostString('message', 'Message');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

//TODO - send the email

// mark our users as contacted update our mysql database
$sql = new Sql ();
$sql->executeStatement("UPDATE `notification_emails` SET contacted = TRUE WHERE album = {$album->getId()};");
$sql->disconnect();
exit ();