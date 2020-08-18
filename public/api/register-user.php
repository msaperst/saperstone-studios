<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $user = User::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
$lastId = $user->create();
echo $lastId;

$_SESSION ['usr'] = $user->getUsername();
$_SESSION ['hash'] = $user->getHash();
// Store some data in the session

$preferences = json_decode($_COOKIE['CookiePreferences']);
if ($_POST['rememberMe'] && in_array("preferences", $preferences)) {
    // remember the user if prompted
    $_COOKIE['hash'] = $user->getHash();
    $_COOKIE ['usr'] = $user->getUsername();
    setcookie('hash', $user->getHash(), time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    setcookie('usr', $user->getUsername(), time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
}

$sql = new Sql ();
$sql->executeStatement("UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE hash='{$user->getHash()}';");
sleep(1);   //why are we sleeping?
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( $lastId, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );");
$sql->disconnect();

$to = "{$user->getName()} <{$user->getEmail()}>";
$from = "noreply@saperstonestudios.com";
$subject = "Thank you for Registering with Saperstone Studios";
$email = new Email($to, $from, $subject);
$text = "Congratulations for registering an account with Saperstone Studios. ";
$text .= "You can login and access the site at https://saperstonestudios.com.";
$html = "<html><body>";
$html = "Congratulations for registering an account with Saperstone Studios. ";
$html .= "You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>.";
$html .= "</html></body>";
$email->setText($text);
$email->setHtml($html);
$email->sendEmail();
exit ();