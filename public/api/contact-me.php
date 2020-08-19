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

try {
    $name = $api->retrievePostString('name', 'Name');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $phone = $api->retrievePostString('phone', 'Phone number');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $emailA = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $message = $api->retrievePostString('message', 'Message');
} catch (Exception $e) {
    echo $e->getMessage();
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
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing...
}

// also send confirmation to user
$subject = "Thank you for contacting Saperstone Studios";
$text = "Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.";
$html = "<html><body>$text</body></html>";
$from = "noreply@saperstonestudios.com";
$to = "$name <$emailA>";

$email = new Email($to, $from, $subject);
$email->setHtml($html);
$email->setText($text);
try {
    $email->sendEmail();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo "There was a problem submitting your message. Please try <a class='gen' href=''>reloading</a> the page and resubmitting it, or <a class='gen' href='mailto:contact@saperstonestudios.com'>contact us</a> to resolve the issue.";
    exit();
}

echo "Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.";
exit ();