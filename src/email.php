<?php

require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/strings.php";
$string = new Strings ();

$email_user_creds = 'EMAIL_USERNAME';
$email_pass_creds = 'EMAIL_PASSWORD';

// in order to ensure LA gets these sent emails in her inbox, we'll send them from her ss gmail instead of @ss domain if they're to and from her
if( $string->endsWith($to, "@saperstonestudios.com>") ) {
    $email_user_creds = 'EMAIL_USERNAME_BACKUP';
    $email_pass_creds = 'EMAIL_PASSWORD_BACKUP';
}

$headers = array(
    'Reply-To' => $from,
    'From' => $from,
    'To' => $to,
    'Subject' => $subject
);
$headers = $mime->headers($headers);
$smtp = Mail::factory('smtp', array(
    'host' => getenv('EMAIL_HOST'),
    'port' => getenv('EMAIL_PORT'),
    'auth' => true,
    'username' => getenv($email_user_creds),
    'password' => getenv($email_pass_creds)
));

$mail = $smtp->send($to, $headers, $body);

if (!file_exists('/var/www/logs')) {
    mkdir("/var/www/logs", 0700);
}
$myFile = "/var/www/logs/emails.txt";

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