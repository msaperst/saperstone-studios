<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $user = User::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
echo $user->create();

$to = "{$user->getName()} <{$user->getEmail()}>";
$from = "noreply@saperstonestudios.com";
$subject = "New User Created at Saperstone Studios";
$email = new Email($to, $from, $subject);
$text = "Someone has setup a new user for you at Saperstone Studios. ";
$text .= "You can login and access the site at https://saperstonestudios.com. ";
$text .= "Initial credentials have been setup for you as: \n";
$text .= "    Username: {$user->getUsername()}\n";
$text .= "    Password: {$user->getPassword()}\n";
$text .= "For security reasons, once logged in, we recommend you reset your password at ";
$text .= "https://saperstonestudios.com/user/profile.php";
$html = "<html><body>";
$html .= "Someone has setup a new user for you at Saperstone Studios. ";
$html .= "You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>. ";
$html .= "Initial credentials have been setup for you as: ";
$html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;Username: {$user->getUsername()}";
$html .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;Password: {$user->getPassword()}</p>";
$html .= "For security reasons, once logged in, we recommend you <a href='https://saperstonestudios.com/user/profile.php'>reset your password</a>.";
$html .= "</html></body>";
$email->setText($text);
$email->setHtml($html);
try {
    $email->sendEmail();
} catch (Exception $e) {
    echo $e->getMessage();
}
exit ();