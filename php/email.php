<?php
$host = "ssl://smtp.verizon.net";
$port = "465";
$username = "vze134ek8";
$password = "Burning1";

$headers = array (
        'Reply-To' => $from,
        'From' => $from,
        'To' => $to,
        'Subject' => $subject 
);
$headers = $mime->headers ( $headers );
$smtp = Mail::factory ( 'smtp', array (
        'host' => $host,
        'port' => $port,
        'auth' => true,
        'username' => $username,
        'password' => $password 
) );

$mail = $smtp->send ( $to, $headers, $body );

// $myFile = "/var/www/V14SS/Includes/Logs/emails.txt";
// $fh = fopen ( $myFile, 'a' ) or die ( "can't open file" );
// fwrite ( $fh, "From: " . $from . "\n" );
// fwrite ( $fh, "To: " . $to . "\n" );
// fwrite ( $fh, "Subject: " . $subject . "\n" );
// fwrite ( $fh, $mime->getTXTBody () . "\n\n" );
// fclose ( $fh );

if (PEAR::isError ( $mail )) {
    $error = $mail->getMessage ();
} else {
    $error = "none";
}
?>