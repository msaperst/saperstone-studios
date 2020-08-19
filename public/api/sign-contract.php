<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api();

$id = $api->retrievePostInt('id', 'Contract id');
if (is_array($id)) {
    echo $id['error'];
    exit();
}

$sql = new Sql ();
$contract_info = $sql->getRow("SELECT * FROM contracts WHERE id = $id;");
if (!$contract_info ['id']) {
    echo "Contract id does not match any contracts";
    $sql->disconnect();
    exit ();
}

$name = $api->retrievePostString('name', 'Contract contact name');
if (is_array($name)) {
    echo $name['error'];
    $sql->disconnect();
    exit();
}

$address = $api->retrievePostString('address', 'Contract contact address');
if (is_array($address)) {
    echo $address['error'];
    $sql->disconnect();
    exit();
}

$number = $api->retrievePostString('number', 'Contract contact number');
if (is_array($number)) {
    echo $number['error'];
    $sql->disconnect();
    exit();
}

$emailA = $api->retrieveValidatedPost('email', 'Contract contact email', FILTER_VALIDATE_EMAIL);
if (is_array($emailA)) {
    echo $emailA['error'];
    $sql->disconnect();
    exit();
}

$signature = $api->retrievePostString('signature', 'Contract signature');
if (is_array($signature)) {
    echo $signature['error'];
    $sql->disconnect();
    exit();
}

$initial = $api->retrievePostString('initial', 'Contract initials');
if (is_array($initial)) {
    echo $initial['error'];
    $sql->disconnect();
    exit();
}

$content = $api->retrievePostString('content', 'Contract content');
if (is_array($content)) {
    echo $content['error'];
    $sql->disconnect();
    exit();
}

$contract_info = $sql->getRow("SELECT * FROM `contracts` WHERE `id` = $id");
$file = "../user/contracts/$name - " . date('Y-m-d') . " - " . ucfirst($contract_info ['type']) . " Contract.pdf";
$sql->executeStatement("UPDATE `contracts` SET `name` = '$name', `address` = '$address', `number` = '$number',
        `email` = '$emailA', `signature` = '$signature', `initial` = '$initial', `content` = '$content', 
        `file` = '$file' WHERE `id` = $id;");
$contract_info = $sql->getRow("SELECT * FROM `contracts` WHERE `id` = $id");
$sql->disconnect();

// sanitize out content
$content = str_replace("\\n", '', $content);
$content = str_replace("\\\"", '"', $content);
$content = str_replace("\\'", '\'', $content);

// look at some formatting
$customCSS = file_get_contents('../css/mpdf.css');

// setup our footer
$footer = "<div align='left'><u>LAS</u>/<img src='$initial' style='height:20px; vertical-align:text-bottom;' /></div>";

// create/save pdf
require dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "resources/autoload.php";
$mpdf = new \Mpdf\Mpdf();
$mpdf->SetHTMLFooter($footer);
$mpdf->WriteHTML($customCSS, 1);
$mpdf->WriteHTML($content);
$mpdf->Output($file);

// email out pdf
$from = "Contracts <contracts@saperstonestudios.com>";
$to = "$name <$emailA>";
$subject = "Saperstone Studios " . ucfirst($contract_info ['type']) . " Contract";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>Thank you for signing your contract. ";
$text = "Thank you for signing your contract. ";
if ($contract_info ['deposit'] > 0) {
    $html .= "Please note you have a $" . $contract_info ['deposit'] . " deposit due. ";
    $text .= "Please note you have a $" . $contract_info ['deposit'] . " deposit due. ";
}
if ($contract_info ['invoice'] != null && $contract_info ['invoice'] != "") {
    $html .= "You can pay your invoice online <a href='" . $contract_info ['invoice'] . "' target='_blank'>here</a>.";
    $text .= "You can pay your invoice online at " . $contract_info ['invoice'] . ".";
}
$html .= "</p>";
$text .= "\n\n";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
$email->addAttachment($file);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}

// now send to LAS
$to = "Contracts <contracts@saperstonestudios.com>";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>$name has signed their contract, this is a copy of it for your records. ";
$text .= "$name has signed their contract, this is a copy of it for your records. ";
if ($contract_info ['deposit'] > 0) {
    $html .= "Don't forget that they have a $" . $contract_info ['deposit'] . " deposit due. ";
    $text .= "Don't forget that they have a $" . $contract_info ['deposit'] . " deposit due. ";
}
$html .= "</p>";
$text .= "\n\n";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
$email->addAttachment($file);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}

exit ();