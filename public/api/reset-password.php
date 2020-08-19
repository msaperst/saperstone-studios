<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
if (is_array($email)) {
    echo $email['error'];
    exit();
}

try {
    $code = $api->retrievePostString('code', 'Code');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $password = $api->retrievePostString('password', 'Password');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $passwordConfirm = $api->retrievePostString('passwordConfirm', 'Password confirmation');
} catch (Exception $e) {
    echo $e->getMessage();
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