<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

// check if fields passed are empty
$sql = new Sql ();
if (isset ($_POST ['name']) && $_POST ['name'] != "") {
    $name = $sql->escapeString($_POST ['name']);
}

if (isset ($_POST ['email']) && $_POST ['email'] != "") {
    $email = $sql->escapeString($_POST ['email']);
}

if (isset ($_POST ['error']) && $_POST ['error'] != "") {
    $error = $sql->escapeString($_POST ['error']);
} else {
    echo "Error is required";
    exit ();
}

if (isset ($_POST ['page']) && $_POST ['page'] != "") {
    $page = $sql->escapeString($_POST ['page']);
} else {
    echo "A page is required";
    exit ();
}

if (isset ($_POST ['referrer']) && $_POST ['referrer'] != "") {
    $referrer = $sql->escapeString($_POST ['referrer']);
} else {
    echo "A referrer is required";
    exit ();
}

$resolution = "";
if (isset ($_POST ['resolution']) && $_POST ['resolution'] != "") {
    $resolution = $sql->escapeString($_POST ['resolution']);
}
$sql->disconnect();

$IP = $session->getClientIP();
$geo_info = json_decode(file_get_contents("http://ipinfo.io/$IP/json"));
$browser = new Browser ();

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
    $id = $systemUser->getId();
    $name = $systemUser->getName();
    $email = $systemUser->getEmail();
    $html .= "<strong>User Id</strong>: $id<br/>";
    $html .= "<strong>Name</strong>: $name<br/>";
    $html .= "<strong>Email</strong>: <a href='mailto:$email'>$email</a><br/>";
}
$html .= $email->getUserInfoHtml();

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "\t\tSomeone got a $error on page $page.\n";
$text .= "\t\tThey came from page $referrer.\n";
$text .= "\t\tYou might want to look into this or take action.\n";
$text .= "\t\tUser information is collected before.\n\n";
if ($systemUser->isLoggedIn()) {
    $id = $systemUser->getId();
    $name = $systemUser->getName();
    $email = $systemUser->getEmail();
    $text .= "User Id: $id\n";
    $text .= "Name: $name\n";
    $text .= "Email: $email\n";
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