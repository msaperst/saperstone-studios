<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();

// determine our user
if ($systemUser->isLoggedIn()) {
    $user = $systemUser->getId();
} else {
    $user = "NULL";
}

// get some location information
$ip = $session->getClientIP();

// get our browser information
$browser = new Browser ();

$width = $height = "NULL";
// get some additional screen information
if (isset ($_GET ['resolution']) && strpos($_GET ['resolution'], 'x') !== false) {
    $temp = explode("x", urldecode($_GET ['resolution']));
    $width = "'" . $temp [0] . "'";
    $height = "'" . $temp [1] . "'";
}

// get where we came from
$referrer = "";
if (isset ($_SERVER ['HTTP_REFERER'])) {
    $referrer = $_SERVER ['HTTP_REFERER'];
}

// check for our boolean values
$isAol = (int)$browser->isAol();
$isFacebook = (int)$browser->isFacebook();
$isMobile = (int)$browser->isMobile();
$isRobot = (int)$browser->isRobot();
$isTablet = (int)$browser->isTablet();

$sql = new Sql ();
$sql->executeStatement("INSERT INTO `usage` (`user`, `ip`, `browser`, `version`, `width`, `height`, `os`, `url`, `isTablet`, `isMobile`, `isAOL`, `isFacebook`, `isRobot`, `ua`) VALUES ($user, '$ip', '{$browser->getBrowser()}', '{$browser->getVersion()}', $width, $height, '{$browser->getPlatform()}', '$referrer', $isTablet, $isMobile, $isAol, $isFacebook, $isRobot, '{$browser->getUserAgent()}');");
$sql->disconnect();
exit ();