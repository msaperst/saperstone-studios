<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

// check if fields passed are empty
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = mysqli_real_escape_string ( $conn->db, $_POST ['name'] );
}

if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
}

if (isset ( $_POST ['error'] ) && $_POST ['error'] != "") {
    $error = mysqli_real_escape_string ( $conn->db, $_POST ['error'] );
} else {
    echo "Error is required";
    exit ();
}

if (isset ( $_POST ['page'] ) && $_POST ['page'] != "") {
    $page = mysqli_real_escape_string ( $conn->db, $_POST ['page'] );
} else {
    echo "A page is required";
    exit ();
}

if (isset ( $_POST ['referrer'] ) && $_POST ['referrer'] != "") {
    $referrer = mysqli_real_escape_string ( $conn->db, $_POST ['referrer'] );
} else {
    echo "A referrer is required";
    exit ();
}

$resolution = "";
if (isset ( $_POST ['resolution'] ) && $_POST ['resolution'] != "") {
    $resolution = mysqli_real_escape_string ( $conn->db, $_POST ['resolution'] );
}

require_once "Mail.php";
require_once "Mail/mime.php";

$IP = $_SERVER ['REMOTE_ADDR'];
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
require_once ($path = dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "resources/Browser.php-master/lib/Browser.php");
$browser = new Browser ();

// create email body and send it
$to = "Webmaster <msaperst@gmail.com>";
$from = "Error <error@saperstonestudios.com>";
$subject = "$error Error";

$html = "<html><body>";
$html .= "This is an automatically generated message from Saperstone Studios<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a $error on page $page.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page $referrer.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action.<br/>";
$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before.<br/><br/>";

if ($user->isLoggedIn ()) {
    $id = $user->getId ();
    $name = $user->getName ();
    $email = $user->getEmail ();
    $html .= "<strong>User Id</strong>: $id<br/>";
    $html .= "<strong>Name</strong>: $name<br/>";
    $html .= "<strong>Email</strong>: <a href='mailto:$email'>$email</a><br/>";
}
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
$html .= "</body></html>";

$text = "This is an automatically generated message from Saperstone Studios\n";
$text .= "\t\tSomeone got a $error on page $page.\n";
$text .= "\t\tThey came from page $referrer.\n";
$text .= "\t\tYou might want to look into this or take action.\n";
$text .= "\t\tUser information is collected before.\n\n";
if ($user->isLoggedIn ()) {
    $id = $user->getId ();
    $name = $user->getName ();
    $email = $user->getEmail ();
    $text .= "User Id: $id\n";
    $text .= "Name: $name\n";
    $text .= "Email: $email\n";
}
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

$crlf = "\n";

$mime = new Mail_mime ( $crlf );

$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );

$body = $mime->get ();

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";
exit ();