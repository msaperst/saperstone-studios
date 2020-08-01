<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$what = $api->retrievePostString('what', 'What to download');
if( is_array( $what ) ) {
    echo json_encode( $what );
    exit();
}

$album = $api->retrievePostInt('album', 'Album id');
if( is_array( $album ) ) {
    echo json_encode( $album );
    exit();
}
$album_info = $sql->getRow( "SELECT * FROM `albums` WHERE id = '$album';" );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    echo json_encode ( array( 'error' => 'Album id does not match any albums' ) );
    $sql->disconnect ();
    exit ();
}

// check for album access
$isAlbumDownloadable = $sql->getRowCount( "SELECT * FROM `download_rights` WHERE user = '0' AND album = '" . $album . "';" );
if (! $user->isLoggedIn () && ! $isAlbumDownloadable) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

$userid = getClientIP();
if ($user->isLoggedIn ()) {
    $userid = $user->getId ();
}

// determine what the user can download
$downloadable = array ();
foreach ( $sql->getRows( "SELECT * FROM `download_rights` WHERE `user` = '" . $user->getId () . "' OR `user` = '0';" ) as $r ) {
    if ($r ['album'] == "*" || ($r ['album'] == $album && $r ['image'] == "*")) {
        $downloadable = $sql->getRows( "SELECT * FROM album_images WHERE album = $album;" );
    } elseif ($r ['album'] == $album) {
        $downloadable = $sql->getRows( "SELECT * FROM album_images WHERE album = $album AND sequence = " . $r ['image'] . ";" );
    }
}
$downloadable = array_unique ( $downloadable, SORT_REGULAR );

// determine what the user wants to download
$desired = array ();
if ($what == "all") {
    $desired = $sql->getRows( "SELECT album_images.* FROM album_images WHERE album = '$album';" );
} elseif ($what == "favorites") {
    $desired = $sql->getRows( "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$userid' AND favorites.album = '$album';" );
} else {
    $desired = $sql->getRows( "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';" );
}

// determine what we will download
$available = array ();
if ($user->isAdmin ()) {    // if we're an admin, we can download all files
    $available = $desired;
} else {    // check to see which files we want to download, we can download
    foreach ( $desired as $file ) {
        $result = doesArrayContainFile ( $downloadable, $file );
        if ($result) {
            $available [] = $file;
        }
    }
}

if (empty ( $available )) {
    $response ['error'] = "There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.";
    echo json_encode ( $response );
    $sql->disconnect ();
    exit ();
}

// for each available file, zip it, and then download it
$images = "";
$image_array = array ();
foreach ( $available as $image ) {
    $file = $image ['location'];
    if (file_exists ( dirname ( ".." . $file ) . "/full/" . basename ( $file ) )) {
        $images .= dirname ( ".." . $file ) . "/full/" . basename ( $file ) . " ";
        $image_array [] = basename ( $file );
    } elseif (file_exists ( ".." . $file )) {
        $images .= ".." . $file . " ";
        $image_array [] = basename ( $file );
    }
}
if ($images == "") {
    $response ['error'] = "No files exist for you to download. Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a>.";
    echo json_encode ( $response );
    $sql->disconnect ();
    exit ();
}
if (! is_dir ( "../tmp/" )) {
    mkdir ( "../tmp/" );
}
$myFile = "../tmp/" . $album_info ['name'] . " " . date ( "Y-m-d H-i-s" ) . ".zip";
$command = `zip -j "$myFile" $images`;
$response ['file'] = $myFile;
echo json_encode ( $response );

// update our user records table
$sql->executeStatement( "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Downloaded', '" . implode ( "\n", $image_array ) . "', $album );" );

// send email
$IP = getClientIP();
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
require_once ($path = dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "resources/Browser.php-master/src/Browser.php");
$browser = new Browser ();
$from = "Actions <actions@saperstonestudios.com>";
$to = "Actions <actions@saperstonestudios.com>";
$subject = "Someone Downloaded Something";

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>Downloads have been made from the <a href='" . $_SERVER ['HTTP_REFERER'] . "' target='_blank'>" . $album_info ['name'] . "</a> album</p>";
$text .= "Downloads have been made from the " . $album_info ['name'] . " album at " . $_SERVER ['HTTP_REFERER'] . "\n\n";
$html .= "<p><ul><li>" . implode ( "</li><li>", $image_array ) . "</li></ul></p><br/>";
$text .= implode ( "\n", $image_array ) . "\n\n";
$html .= "<p><strong>Name</strong>: " . $user->getName () . "<br/>";
$text .= "Name: " . $user->getName () . "\n";
$html .= "<strong>Email</strong>: <a href='mailto:" . $user->getEmail () . "'>" . $user->getEmail () . "</a><br/>";
$text .= "Email: " . $user->getEmail () . "\n";
if (! isset ( $geo_info->city )) {
    $html .= "<strong>Location</strong>: unknown (use $IP to manually lookup)<br/>";
    $text .= "Location: unknown (use $IP to manually lookup)\n";
} else {
    if (isset ( $geo_info->postal )) {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " " . $geo_info->postal . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    } else {
        $html .= "<strong>Location</strong>: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)<br/>";
        $text .= "Location: " . $geo_info->city . ", " . $geo_info->region . " - " . $geo_info->country . " (estimated location based on IP: $IP)\n";
    }
    $html .= "<strong>Hostname</strong>: " . $geo_info->hostname . "<br/>";
    $text .= "Hostname: " . $geo_info->hostname . "\n";
}
if (isset ( $_POST ['position'] ) && isset ( $location )) {
    $html .= "<strong>Location</strong>: $location (estimate based on geolocation $position)<br/>";
    $text .= "Location: $location (estimate based on geolocation $position)\n";
}
$html .= "<strong>Browser</strong>: " . $browser->getBrowser () . " " . $browser->getVersion () . "<br/>";
$text .= "Browser: " . $browser->getBrowser () . " " . $browser->getVersion () . "\n";
$html .= "<strong>OS</strong>: " . $browser->getPlatform () . "<br/>";
$text .= "OS: " . $browser->getPlatform () . "\n";
$html .= "<strong>Full UA</strong>: " . $_SERVER ['HTTP_USER_AGENT'] . "</p>";
$text .= "Full UA: " . $_SERVER ['HTTP_USER_AGENT'];
$html .= "</body></html>";

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";

$sql->disconnect ();
exit ();

// our function to see if an array of files contains the expected file
function doesArrayContainFile($array, $file) {
    foreach ( $array as $element ) {
        $match = true;
        foreach ( $element as $key => $value ) {
            if ($element [$key] != $file [$key]) {
                $match = false;
            }
        }
        if ($match) {
            return true;
        }
    }
    return false;
}