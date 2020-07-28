<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

if (isset ( $_POST ['what'] ) && $_POST ['what'] != "") {
    $what = $sql->escapeString( $_POST ['what'] );
} else {
    if (! isset ( $_POST ['what'] )) {
        $response ['err'] = "What to download is required!";
    } elseif ($_POST ['what'] == "") {
        $response ['err'] = "What to download cannot be blank!";
    } else {
        $response ['err'] = "Some other download error occurred!";
    }
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        $response ['err'] = "Album to download from is required!";
    } elseif ($_POST ['album'] == "") {
        $response ['err'] = "Album to download from cannot be blank!";
    } else {
        $response ['err'] = "Some other download error occurred!";
    }
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `albums` WHERE id = '$album';";
$album_info = $sql->getRow( $sql );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    $response ['err'] = "Album doesn't exist!";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

// check for album access
$isAlbumDownloadable = mysqli_num_rows ( mysqli_query ( $conn->db, "SELECT * FROM `download_rights` WHERE user = '0' AND album = '" . $album . "';" ) );
if (! $user->isLoggedIn () && ! $isAlbumDownloadable) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}
$userid = getClientIP();
if ($user->isLoggedIn ()) {
    $userid = $user->getId ();
}

// determine what the user can download
$downloadable = array ();
$sql = "SELECT * FROM `download_rights` WHERE `user` = '" . $user->getId () . "' OR `user` = '0';";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    if ($r ['album'] == "*" || ($r ['album'] == $album && $r ['image'] == "*")) {
        $sql = "SELECT * FROM album_images WHERE album = $album;";
        $sesult = mysqli_query ( $conn->db, $sql );
        while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
            $downloadable [] = $s;
        }
    } elseif ($r ['album'] == $album) {
        $sql = "SELECT * FROM album_images WHERE album = $album AND sequence = " . $r ['image'] . ";";
        $sesult = mysqli_query ( $conn->db, $sql );
        while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
            $downloadable [] = $s;
        }
    }
}
$downloadable = array_unique ( $downloadable, SORT_REGULAR );

// determine what the user wants to download
if ($what == "all") {
    $sql = "SELECT album_images.* FROM album_images WHERE album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
} elseif ($what == "favorites") {
    $sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$userid' AND favorites.album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
} else {
    $sql = "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $desired [] = $r;
    }
}

$available = array ();
// if we're an admin, we can download all files
if ($user->isAdmin ()) {
    $available = $desired;
    // check to see which files we want to download, we can download
} else {
    foreach ( $desired as $file ) {
        $result = doesArrayContainFile ( $downloadable, $file );
        if ($result) {
            $available [] = $file;
        }
    }
}

if (empty ( $available )) {
    $response ['err'] = "There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.";
    echo json_encode ( $response );
    $conn->disconnect ();
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
    $response ['err'] = "No files exist for you to download. Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a>.";
    echo json_encode ( $response );
    $conn->disconnect ();
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
mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Downloaded', '" . implode ( "\n", $image_array ) . "', $album );" );

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

$conn->disconnect ();
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