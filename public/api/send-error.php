<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $error = $api->retrievePostString('error', 'Error');
} catch ( Exception $e ) {
    echo $e->getMessage();
    exit();
}

try {
    $page = $api->retrievePostString('page', 'Page');
} catch ( Exception $e ) {
    echo $e->getMessage();
    exit();
}

try {
    $referrer = $api->retrievePostString('referrer', 'Referral');
} catch ( Exception $e ) {
    echo $e->getMessage();
    exit();
}

// create email body and send it
$to = "Webmaster <msaperst@gmail.com>";
$from = "Error <error@saperstonestudios.com>";
$subject = "$error Error";

$email = new Email($to, $from, $subject);
$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a $error on page $page.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page $referrer.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before.<br/><br/>";

if ($systemUser->isLoggedIn()) {
    $html .= "<strong>User Id</strong>: {$systemUser->getId()}<br/>";
    $html .= "<strong>Name</strong>: {$systemUser->getName()}<br/>";
    $html .= "<strong>Email</strong>: <a href='mailto:{$systemUser->getEmail()}'>{$systemUser->getEmail()}</a><br/>";
}
$html .= $email->getUserInfoHtml();

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "\t\tSomeone got a $error on page $page.\n";
$text .= "\t\tThey came from page $referrer.\n";
$text .= "\t\tYou might want to look into this or take action.\n";
$text .= "\t\tUser information is collected before.\n\n";
if ($systemUser->isLoggedIn()) {
    $text .= "User Id: {$systemUser->getId()}\n";
    $text .= "Name: {$systemUser->getName()}\n";
    $text .= "Email: {$systemUser->getEmail()}\n";
}
$text .= $email->getUserInfoText();

$email->setHtml($html);
$email->setText($text);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}
exit ();