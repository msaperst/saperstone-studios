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
$sql = new Sql ();
// send out emails to each user, and then mark the db showing they've been updated
$notifications = $sql->getRows("SELECT * FROM notification_emails WHERE album = {$album->getId()} AND contacted = FALSE;");
foreach( $notifications as $notification) {
    $to = $notification['email'];
    if( is_integer($notification['user'])) {
        try {
            $user = User::withId($notification['user']);
            $to = "{$user->getName()} <{$notification['email']}>";
        } catch (Exception $e) {
            //No nothing, it's fine if we don't have the users' name
        }
    }
    $from = "noreply@saperstonestudios.com";
    $subject = "Album Updated on Saperstone Studios";
    $email = new Email($to, $from, $subject);
    $text = "An album you requested to be updated about has been updated.\n\n";
    $text .= $message;
    $html = "<html><body>";
    $html .= $text;
    $html .= "</body></html>";
    $email->setText($text);
    $email->setHtml($html);
    try {
        $email->sendEmail();
        $sql->executeStatement("UPDATE `notification_emails` SET contacted = TRUE WHERE album = {$album->getId()} AND email = '{$notification['email']}';");
    } catch (Exception $e) {
        echo $e->getMessage() . "\n<br/>";
    }
}
$sql->disconnect();
exit ();