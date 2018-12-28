<?php
$headers = array(
    'Reply-To' => $from,
    'From' => $from,
    'To' => $to,
    'Subject' => $subject
);
$headers = $mime->headers($headers);
$smtp = Mail::factory('smtp', array(
    'host' => getenv('EMAIL_HOST'),
    'port' => getenv('DB_PORT'),
    'auth' => true,
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD')
));

$mail = $smtp->send($to, $headers, $body);

if (!file_exists('../logs')) {
    mkdir("../logs", 0700);
}
$myFile = "../logs/emails.txt";

$fh = fopen($myFile, 'a') or die ("can't open file");
fwrite($fh, "From: " . $from . "\n");
fwrite($fh, "To: " . $to . "\n");
fwrite($fh, "Date: " . date('l jS \of F Y h:i:s A') . "\n");
fwrite($fh, "Subject: " . $subject . "\n");
fwrite($fh, $mime->getTXTBody() . "\n");
fwrite($fh, "=====================================================\n");
fwrite($fh, "=====================================================\n");
fclose($fh);

if (PEAR::isError($mail)) {
    $error = $mail->getMessage();
} else {
    $error = "none";
}
?>