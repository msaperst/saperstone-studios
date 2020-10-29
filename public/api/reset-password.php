<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

try {
    $email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL);
    $code = $api->retrievePostString('code', 'Code');
    $password = $api->retrievePostString('password', 'Password');
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
$sql->executeStatement("UPDATE users SET pass='" . md5($password) . "', resetKey=NULL WHERE email='$email' AND resetKey='$code';");
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Reset Password', NULL, NULL );");
$sql->disconnect();
$user->login(false);
exit ();