<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
$sql = new Sql ();

$id;
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = intval ( $_POST ['id'] );
} else {
    echo "Id is not provided";
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM contracts WHERE id = $id;";
$contract_info = $sql->getRow( $sql );
if (! $contract_info ['id']) {
    echo "That ID doesn't match any contracts";
    $conn->disconnect ();
    exit ();
}

$name;
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    echo "Name is not provided";
    $conn->disconnect ();
    exit ();
}

$address;
if (isset ( $_POST ['address'] ) && $_POST ['address'] != "") {
    $address = $sql->escapeString( $_POST ['address'] );
} else {
    echo "Address is not provided";
    $conn->disconnect ();
    exit ();
}

$number;
if (isset ( $_POST ['number'] ) && $_POST ['number'] != "") {
    $number = $sql->escapeString( $_POST ['number'] );
} else {
    echo "Number is not provided";
    $conn->disconnect ();
    exit ();
}

$email;
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = $sql->escapeString( $_POST ['email'] );
} else {
    echo "Email is not provided";
    $conn->disconnect ();
    exit ();
}

$signature;
if (isset ( $_POST ['signature'] ) && $_POST ['signature'] != "") {
    $signature = $sql->escapeString( $_POST ['signature'] );
} else {
    echo "Signature is not provided";
    $conn->disconnect ();
    exit ();
}

$initial;
if (isset ( $_POST ['initial'] ) && $_POST ['initial'] != "") {
    $initial = $sql->escapeString( $_POST ['initial'] );
} else {
    echo "Initial is not provided";
    $conn->disconnect ();
    exit ();
}

$content;
if (isset ( $_POST ['content'] ) && $_POST ['content'] != "") {
    $content = $sql->escapeString( $_POST ['content'] );
} else {
    echo "Content is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `contracts` WHERE `id` = $id";
$contract_info = $sql->getRow( $sql );
$file = "../user/contracts/$name - " . date('Y-m-d') . " - " . ucfirst( $contract_info ['type'] ) . " Contract.pdf";
$sql = "UPDATE `contracts` SET `name` = '$name', `address` = '$address', `number` = '$number',
        `email` = '$email', `signature` = '$signature', `initial` = '$initial', `content` = '$content', 
        `file` = '$file' WHERE `id` = $id;";
mysqli_query ( $conn->db, $sql );
$sql = "SELECT * FROM `contracts` WHERE `id` = $id";
$contract_info = $sql->getRow( $sql );
$conn->disconnect ();

// sanitize out content
$content = str_replace ( "\\n", '', $content );
$content = str_replace ( "\\\"", '"', $content );
$content = str_replace ( "\\'", '\'', $content );

// look at some formatting
$customCSS = file_get_contents ( '../css/mpdf.css' );

// setup our footer
$footer = "<div align='left'><u>LAS</u>/<img src='$initial' style='height:20px; vertical-align:text-bottom;' /></div>";

// create/save pdf
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "resources/autoload.php";
$mpdf = new \Mpdf\Mpdf();
$mpdf->SetHTMLFooter ( $footer );
$mpdf->WriteHTML ( $customCSS, 1 );
$mpdf->WriteHTML ( $content );
$mpdf->Output ( $file );

// email out pdf
$from = "Contracts <contracts@saperstonestudios.com>";
$to = "$name <$email>";
$subject = "Saperstone Studios " . ucfirst ( $contract_info ['type'] ) . " Contract";

$html = "<html><body>";
$html .= "<p>Thank you for signing your contract. ";
$text .= "Thank you for signing your contract. ";
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

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$mime->addAttachment ( $file );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'email.php';

// now send to LAS
$to = "Contracts <contracts@saperstonestudios.com>";
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

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$mime->addAttachment ( $file );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'email.php';

exit ();