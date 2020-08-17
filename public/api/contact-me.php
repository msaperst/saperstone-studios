<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = User::fromSystem();
$api = new Api ();

// TODO - NEED TO CHANGE BACK, JUST GOOD FOR TESTING!!!
$to = "Contact <msaperst+sstest@gmail.com>";
if (isset ($_POST ['to']) && $_POST ['to'] != "") {
    $to = $sql->escapeString($_POST ['to']);
}
$sql->disconnect();

$name = $api->retrievePostString('name', 'Name');
if (is_array($name)) {
    echo $name['error'];
    exit();
}
$phone = $api->retrievePostString('phone', 'Phone number');
if (is_array($phone)) {
    echo $phone['error'];
    exit();
}
$emailA = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
if (is_array($emailA)) {
    echo $emailA['error'];
    exit();
}
$message = $api->retrievePostString('message', 'Message');
if (is_array($message)) {
    echo $message['error'];
    exit();
}

$referrer = "";
if (isset ($_SERVER ['HTTP_REFERER'])) {
    $referrer = $_SERVER ['HTTP_REFERER'];
}

// create email body and send it
$from = "$name <$emailA>";
$subject = "Saperstone Studios Contact Form:  $name";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
$html .= "<strong>Name</strong>: $name<br/>";
$html .= "<strong>Phone</strong>: $phone<br/>";
$html .= "<strong>Email</strong>: <a href='mailto:$emailA'>$emailA</a><br/>";
$html .= $email->getUserInfoHtml();
$html .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$message<br/><br/>";
$html .= "</body></html>";

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "Name: $name\n";
$text .= "Phone: $phone\n";
$text .= "Email: $emailA\n";
$text .= $email->getUserInfoText();
$text .= "\n\t\t$message";

$email->setHtml($html);
$email->setText($text);
$email->sendEmail();

// also send confirmation to user
$subject = "Thank you for contacting Saperstone Studios";
$text = "Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.";
$html = "<html><body>$text</body></html>";
$from = "noreply@saperstonestudios.com";
$to = "$name <$emailA>";

$email = new Email($to, $from, $subject);
$email->setHtml($html);
$email->setText($text);
$error = $email->sendEmail();

if ($error == NULL) {
    echo "Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.";
} else {
    error_log($error);
    echo "There was a problem submitting your message. Please try <a class='gen' href=''>reloading</a> the page and resubmitting it, or <a class='gen' href='mailto:contact@saperstonestudios.com'>contact us</a> to resolve the issue.";
}

exit ();