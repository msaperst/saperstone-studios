<?php
// check if fields passed are empty
if (empty ( $_POST ['name'] ) || empty ( $_POST ['phone'] ) || empty ( $_POST ['email'] ) || empty ( $_POST ['message'] ) || ! filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL )) {
    echo "No arguments Provided!";
    return false;
}

require_once "Mail.php";
require_once "Mail/mime.php";

$name = $_POST ['name'];
$phone = $_POST ['phone'];
$email = $_POST ['email'];
$resolution = $_POST ['resolution'];
$message = $_POST ['message'];
$IP = $_SERVER ['REMOTE_ADDR'];
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
$browser = get_browser ( null, true );
// print_r($browser);
$referrer = "";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referrer = $_SERVER ['HTTP_REFERER'];
}

// create email body and send it
// $to = "Contact <contact@saperstonestudios.com>"; // PUT YOUR EMAIL ADDRESS HERE
$to = "Mr Max <msaperst@gmail.com>"; // PUT YOUR EMAIL ADDRESS HERE
$from = "$name <$email>";
$subject = "Saperstone Studios Contact Form:  $name"; // EDIT THE EMAIL SUBJECT LINE HERE

$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
// $html .= "The below party contacted you about $topic<br/>";
$html .= "<strong>Name</strong>: $name<br/>";
$html .= "<strong>Email</strong>: <a href='mailto:$email'>$email</a><br/>";
if (! isset ( $geo_info->city )) {
    $html .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
} else {
    if (isset ( $geo_info->postal )) {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
    } else {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
    }
    $html .= "<strong>Organization</strong>: " . $geo_info->org . "<br/>";
    $html .= "<strong>Hostname</strong>: " . $geo_info->hostname . "<br/>";
}
if (isset ( $_POST ['position'] ) && isset ( $location )) {
    $html .= "<strong>Location</strong>: $location (estimate based on geolocation $position)<br/>";
}
$html .= "<strong>Browser</strong>: " . $browser ['parent'] . "<br/>";
$html .= "<strong>Resoluation</strong>: $resolution<br/>";
$html .= "<strong>OS</strong>: " . $browser ['platform'] . "<br/>";
// $html .= "<strong>IP</strong>: $IP<br/>";
$html .= "<strong>Full UA</strong>: " . $_SERVER ['HTTP_USER_AGENT'] . "<br/>";
$html .= "<strong>Submitted From</strong>: <a href='$referrer'>$referrer</a><br/>";
$html .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$message<br/><br/>";
$html .= "</body></html>";

$text = "This is an automatically generated message from Saperstone Studios\n";
// $text .= "The below party contacted you about $topic\n";
$text .= "Name: $name\n";
$text .= "Email: $email\n";
if (! isset ( $geo_info->city )) {
    $text .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
} else {
    if (isset ( $geo_info->postal )) {
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    } else {
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    }
    $text .= "Organization: " . $geo_info->org . "\n";
    $text .= "Hostname: " . $geo_info->hostname . "\n";
}
if (isset ( $_POST ['position'] ) && isset ( $location )) {
    $text .= "Location: $location (estimate based on geolocation $position)\n";
}
$text .= "Browser: " . $browser ['parent'] . "\n";
$text .= "Resoluation: $resolution";
$text .= "OS: " . $browser ['platform'] . "\n";
// $text .= "IP: $IP\n";
$text .= "Full UA: " . $_SERVER ['HTTP_USER_AGENT'] . "\n";
$text .= "Submitted From: $referrer\n";
$text .= "\n\t\t$message";

$crlf = "\n";

$mime = new Mail_mime ( $crlf );

$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );

$body = $mime->get ();

require ('../php/email.php');

// also send confirmation to user
$subject = "Thank you for contacting Saperstone Studios";
$text = "Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.";
$html = "<html><body>$text</body></html>";
$from = $to;
$to = "$name <$email>";

$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require ('../php/email.php');

if ($error == "none") {
    echo "Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.";
} else {
    error_log ( $error );
    echo "There was a problem submitting your message. Please try <a class='gen' href=''>reloading</a> the page and resubmitting it, or <a class='gen' href='mailto:contact@saperstonestudios.com'>contact us</a> to resolve the issue.";
}

exit ();