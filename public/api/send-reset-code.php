<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";

$string = new Strings ();

$email = "";
if (isset ($_POST ['email'])) {
    $email = $sql->escapeString($_POST ['email']);
} else {
    echo "Enter an email address!";
    exit ();
}

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $resetCode = $string->randomString(8);
    mysqli_query($conn->db, "UPDATE users SET resetKey='$resetCode' WHERE email='$email';");
    $row = $sql->getRow("SELECT firstName, lastName FROM users WHERE email='$email';");
    $name = "";
    if ($row ['firstName']) {
        $name .= $row ['firstName'];
    }
    if ($row ['lastName']) {
        $name .= " " . $row ['lastName'];
    }

    if (is_array($row)) {
        $subject = "Reset Key For Saperstone Studios Account";
        $text = "You requested a reset key for your saperstone studios account. Enter the key below to reset your password. If you did not request this key, disregard this message.\n\n";
        $text .= "\t$resetCode";
        $html = "<html><body>" . $string->textToHTML($text) . "</body></html>";
        $from = "noreply@saperstonestudios.com";
        $to = "$name <$email>";

        $mime = new Mail_mime ($crlf);
        $mime->setTXTBody($text);
        $mime->setHTMLBody($html);
        $body = $mime->get();
        require dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'email.php';
    }

    $conn->disconnect();
    exit ();
} else {
    echo "Enter a valid email address!";
    $conn->disconnect();
    exit ();
}
?>