<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    $contract = Contract::withId($_POST['id']);
    $file = $contract->sign($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

// email out pdf
$from = "Contracts <contracts@saperstonestudios.com>";
$to = "{$contract->getName()} <{$contract->getEmail()}>";
$subject = "Saperstone Studios " . ucfirst($contract->getType()) . " Contract";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>Thank you for signing your contract. ";
$text = "Thank you for signing your contract. ";
if ($contract->getDeposit() > 0) {
    $html .= "Please note you have a \${$contract->getDeposit()} deposit due. ";
    $text .= "Please note you have a \${$contract->getDeposit()} deposit due. ";
}
if ($contract->getInvoice() != NULL && $contract->getInvoice() != "") {
    $html .= "You can pay your invoice online <a href='{$contract->getInvoice()}' target='_blank'>here</a>.";
    $text .= "You can pay your invoice online at {$contract->getInvoice()}.";
}
$html .= "</p>";
$text .= "\n\n";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
$email->addAttachment(dirname(__DIR__) . $file);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}

// now send to LAS
$to = 'Contracts <' . getenv('EMAIL_CONTRACTS') . '>';
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>{$contract->getName()} has signed their contract, this is a copy of it for your records. ";
$text .= "{$contract->getName()} has signed their contract, this is a copy of it for your records. ";
if ($contract->getDeposit() > 0) {
    $html .= "Don't forget that they have a \${$contract->getDeposit()} deposit due. ";
    $text .= "Don't forget that they have a \${$contract->getDeposit()} deposit due. ";
}
$html .= "</p>";
$text .= "\n\n";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
$email->addAttachment(dirname(__DIR__) . $file);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}

exit ();