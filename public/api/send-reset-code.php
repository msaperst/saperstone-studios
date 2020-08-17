<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();

$emailA = "";
if (isset ($_POST ['email'])) {
    $emailA = $sql->escapeString($_POST ['email']);
} else {
    echo "Enter an email address!";
    exit ();
}

if (filter_var($emailA, FILTER_VALIDATE_EMAIL)) {
    $resetCode = Strings::randomString(8);
    mysqli_query($conn->db, "UPDATE users SET resetKey='$resetCode' WHERE email='$emailA';");
    $row = $sql->getRow("SELECT firstName, lastName FROM users WHERE email='$emailA';");
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
        $html = "<html><body>" . Strings::textToHTML($text) . "</body></html>";
        $from = "noreply@saperstonestudios.com";
        $to = "$name <$emailA>";

        $email = new Email($to, $from, $subject);
        $email->setText($text);
        $email->setHtml($html);
        $email->sendEmail();
    }

    $conn->disconnect();
    exit ();
} else {
    echo "Enter a valid email address!";
    $conn->disconnect();
    exit ();
}
?>