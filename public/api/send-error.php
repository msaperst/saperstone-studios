<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $error = $api->retrievePostString('error', 'Error');
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

try {
    $page = $api->retrievePostString('page', 'Page');
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

try {
    $referrer = $api->retrievePostString('referrer', 'Referral');
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo $e->getMessage();
    exit();
}

// create email body and send it
$to = "Webmaster <msaperst@gmail.com>";
$from = "Error <error@saperstonestudios.com>";
$subject = "$error Error";

function formatErrorEmailLink(string $value): string {
    $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return $escaped;
    }

    $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return $escaped;
    }

    return "<a href='$escaped' target='_blank'>$escaped</a>";
}

$pageHtml = formatErrorEmailLink($page);
$referrerHtml = formatErrorEmailLink($referrer);

$email = new Email($to, $from, $subject);
$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a $error on page $pageHtml<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page $referrerHtml.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/>";

if ($systemUser->isLoggedIn()) {
    $html .= "<strong>User Id</strong>: {$systemUser->getId()}<br/>";
    $html .= "<strong>Name</strong>: {$systemUser->getName()}<br/>";
    $html .= "<strong>Email</strong>: <a href='mailto:{$systemUser->getEmail()}'>{$systemUser->getEmail()}</a><br/>";
}
$html .= $email->getUserInfoHtml();
$html .= "</body></html>";

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "\t\tSomeone got a $error on page $page\n";
$text .= "\t\tThey came from page $referrer\n";
$text .= "\t\tYou might want to look into this or take action\n";
$text .= "\t\tUser information is collected before\n\n";
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
