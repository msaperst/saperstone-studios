<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
$sql = new Sql ();

$to = "Contact <contact@saperstonestudios.com>";
if (isset ( $_POST ['to'] ) && $_POST ['to'] != "") {
    $to = $sql->escapeString( $_POST ['to'] );
}

// check if fields passed are empty
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    if (! isset ( $_POST ['name'] )) {
        echo "Name is required";
    } elseif ($_POST ['name'] == "") {
        echo "Name can not be blank";
    } else {
        echo "Some other name error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['phone'] ) && $_POST ['phone'] != "") {
    $phone = $sql->escapeString( $_POST ['phone'] );
} else {
    if (! isset ( $_POST ['phone'] )) {
        echo "Phone is required";
    } elseif ($_POST ['phone'] == "") {
        echo "Phone can not be blank";
    } else {
        echo "Some other phone error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['email'] ) && filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL ) ) {
    $email = $sql->escapeString( $_POST ['email'] );
} else {
    if (! isset ( $_POST ['email'] )) {
        echo "Email is required";
    } elseif ($_POST ['email'] == "") {
        echo "Email can not be blank";
    } elseif ( ! filter_var ( $_POST ['email'], FILTER_VALIDATE_EMAIL ) ) {
        echo "Email is not valid";
    } else {
        echo "Some other email error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['message'] ) && $_POST ['message'] != "") {
    $message = $sql->escapeString( $_POST ['message'] );
} else {
    if (! isset ( $_POST ['message'] )) {
        echo "Message is required";
    } elseif ($_POST ['message'] == "") {
        echo "Message can not be blank";
    } else {
        echo "Some other message error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$resolution = "";
if (isset ( $_POST ['resolution'] ) && $_POST ['resolution'] != "") {
    $resolution = $sql->escapeString( $_POST ['resolution'] );
}
$sql->disconnect ();

require_once "Mail.php";
require_once "Mail/mime.php";

$IP = getClientIP();
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
require_once ($path = dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "resources/Browser.php-master/src/Browser.php");
$browser = new Browser ();
$referrer = "";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referrer = $_SERVER ['HTTP_REFERER'];
}

// create email body and send it
$from = "$name <$email>";
$subject = "Saperstone Studios Contact Form:  $name";

$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
$html .= "<strong>Name</strong>: $name<br/>";
$html .= "<strong>Phone</strong>: $phone<br/>";
$html .= "<strong>Email</strong>: <a href='mailto:$email'>$email</a><br/>";
if (! isset ( $geo_info->city )) {
    $html .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
} else {
    if (isset ( $geo_info->postal )) {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
    } else {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
    }
    $html .= "<strong>Hostname</strong>: " . $geo_info->hostname . "<br/>";
}
if (isset ( $_POST ['position'] ) && isset ( $location )) {
    $html .= "<strong>Location</strong>: $location (estimate based on geolocation $position)<br/>";
}
$html .= "<strong>Browser</strong>: " . $browser->getBrowser () . " " . $browser->getVersion () . "<br/>";
$html .= "<strong>Resoluation</strong>: $resolution<br/>";
$html .= "<strong>OS</strong>: " . $browser->getPlatform () . "<br/>";
$html .= "<strong>Full UA</strong>: " . $_SERVER ['HTTP_USER_AGENT'] . "<br/>";
$html .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$message<br/><br/>";
$html .= "</body></html>";

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "Name: $name\n";
$text .= "Phone: $phone\n";
$text .= "Email: $email\n";
if (! isset ( $geo_info->city )) {
    $text .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
} else {
    if (isset ( $geo_info->postal )) {
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    } else {
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    }
    $text .= "Hostname: " . $geo_info->hostname . "\n";
}
if (isset ( $_POST ['position'] ) && isset ( $location )) {
    $text .= "Location: $location (estimate based on geolocation $position)\n";
}
$text .= "Browser: " . $browser->getBrowser () . " " . $browser->getVersion () . "\n";
$text .= "Resoluation: $resolution";
$text .= "OS: " . $browser->getPlatform () . "\n";
$text .= "Full UA: " . $_SERVER ['HTTP_USER_AGENT'] . "\n";
$text .= "\n\t\t$message";

$crlf = "\n";

$mime = new Mail_mime ( $crlf );

$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );

$body = $mime->get ();

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";

// also send confirmation to user
$subject = "Thank you for contacting Saperstone Studios";
$text = "Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.";
$html = "<html><body>$text</body></html>";
$from = "noreply@saperstonestudios.com";
$to = "$name <$email>";

$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";

if ($error == "none") {
    echo "Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.";
} else {
    error_log ( $error );
    echo "There was a problem submitting your message. Please try <a class='gen' href=''>reloading</a> the page and resubmitting it, or <a class='gen' href='mailto:contact@saperstonestudios.com'>contact us</a> to resolve the issue.";
}

exit ();