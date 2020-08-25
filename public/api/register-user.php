<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    $user = User::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
$lastId = $user->create();
echo $lastId;

sleep(1);   //put in a sleep, otherwise there will be a conflict in the user log
$user->login(false);

$to = "{$user->getName()} <{$user->getEmail()}>";
$from = "noreply@saperstonestudios.com";
$subject = "Thank you for Registering with Saperstone Studios";
$email = new Email($to, $from, $subject);
$text = "Congratulations for registering an account with Saperstone Studios. ";
$text .= "You can login and access the site at https://saperstonestudios.com.";
$html = "<html><body>";
$html = "Congratulations for registering an account with Saperstone Studios. ";
$html .= "You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>.";
$html .= "</html></body>";
$email->setText($text);
$email->setHtml($html);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}
exit ();