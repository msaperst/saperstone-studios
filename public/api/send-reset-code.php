<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

try {
    $email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $user = User::fromEmail($email);
} catch (Exception $e) {
    echo "Credentials do not match our records";
    exit();
}

$resetCode = $user->setResetCode();

$subject = "Reset Key For Saperstone Studios Account";
$text = "You requested a reset key for your saperstone studios account. Enter the key below to reset your password. If you did not request this key, disregard this message.\n\n";
$text .= "\t$resetCode";
$html = "<html><body>" . Strings::textToHTML($text) . "</body></html>";
$from = "noreply@saperstonestudios.com";
$to = "{$user->getName()} <{$user->getEmail()}>";
$email = new Email($to, $from, $subject);
$email->setText($text);
$email->setHtml($html);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}
exit();