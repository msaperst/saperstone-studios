<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
if (is_array($email)) {
    echo $email['error'];
    exit();
}

$code = $api->retrievePostString('code', 'Code');
if (is_array($code)) {
    echo $code['error'];
    exit();
}

$password = $api->retrievePostString('password', 'Password');
if (is_array($password)) {
    echo $password['error'];
    exit();
}

$passwordConfirm = $api->retrievePostString('passwordConfirm', 'Password confirmation');
if (is_array($passwordConfirm)) {
    echo $passwordConfirm['error'];
    exit();
}

if ($password != $passwordConfirm) {
    echo "Password and confirmation do not match";
    exit();
}

try {
    $user = User::fromReset($email, $code);
} catch (Exception $e) {
    echo "Credentials do not match our records";
    exit();
}
$sql = new Sql();
$sql->executeStatement("UPDATE users SET pass='" . md5($password) . "' WHERE email='$email' AND resetKey='$code';");
$sql->executeStatement("UPDATE users SET resetKey=NULL WHERE email='$email';");
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Reset Password', NULL, NULL );");
$sql->disconnect();
$user->login(false);
exit ();