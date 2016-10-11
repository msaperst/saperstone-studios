<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

// determine our user;
if ($user->isLoggedIn ()) {
    $user = $user->getId ();
} else {
    $user = "";
}

$resolution = $position = $lat = $lon = $width = $height = "";
// get some location information
$ip = $_SERVER ['REMOTE_ADDR'];
if (isset ( $_GET ['position'] )) {
    $position = urldecode ( $_GET ['position'] );
    $temp = split ( ",", $position );
    $lat = $temp [0];
    $lon = $temp [1];
}

// get our browser information
require_once ($path = '../plugins/Browser.php-master/lib/Browser.php');
$browser = new Browser ();

// get some additional screen information
if (isset ( $_GET ['resolution'] )) {
    $resolution = urldecode ( $_GET ['resolution'] );
    $temp = split ( "x", $resolution );
    $width = $temp [0];
    $height = $temp [1];
}

// get where we came from
$referrer = "";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referrer = $_SERVER ['HTTP_REFERER'];
}

// check for our null values
$user = ! empty ( $user ) ? "'$user'" : "NULL";
$lat = ! empty ( $lat ) ? "'$lat'" : "NULL";
$lon = ! empty ( $lon ) ? "'$lon'" : "NULL";
$width = ! empty ( $width ) ? "'$width'" : "NULL";
$height = ! empty ( $height ) ? "'$height'" : "NULL";

// check foor our boolean values
$isAol = $browser->isAol () ? "1" : "0";
$isFacebook = $browser->isFacebook () ? "1" : "0";
$isMobile = $browser->isMobile () ? "1" : "0";
$isRobot = $browser->isRobot () ? "1" : "0";
$isTablet = $browser->isTablet () ? "1" : "0";

require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();
if ($lat == "NULL" && $lon == "NULL") {
    $sql = "INSERT INTO `usage` (`user`, `ip`, `browser`, `version`, `width`, `height`, `os`, `url`, `isTablet`, `isMobile`, `isAOL`, `isFacebook`, `isRobot`, `ua`) VALUES ($user, '$ip', '" . $browser->getBrowser () . "', '" . $browser->getVersion () . "', $width, $height, '" . $browser->getPlatform () . "', '$referrer', '$isTablet', '$isMobile', '$isAol', '$isFacebook', '$isRobot', '" . $browser->getUserAgent () . "');";
} else {
    $sql = "UPDATE `usage` SET `latitude`=$lat,`longitude`=$lon WHERE `user` = $user AND `ip`='$ip' AND `time` >= CURRENT_TIMESTAMP - INTERVAL 60 MINUTE AND `browser`='" . $browser->getBrowser () . "' AND `version`='" . $browser->getVersion () . "' AND `width`=$width AND `height`=$height AND `os`='" . $browser->getPlatform () . "' AND `title`='$title' AND `url`='$referrer' AND `isTablet`='$isTablet' AND `isMobile`='$isMobile' AND `isAOL`='$isAol'  AND `isFacebook`='$isFacebook' AND `isRobot`='$isRobot' AND `ua`='" . $browser->getUserAgent () . "';";
}
mysqli_query ( $conn->db, $sql );
$conn->disconnect ();
exit ();