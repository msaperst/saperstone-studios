<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $email = $api->retrievePostString('email', 'Email address');
    $file = $api->retrievePostString('file', 'Image file');
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
    exit();
}

system("bash -c 'sleep 300; php -f ../../bin/send-download-email.php $email \"$file\";' > /dev/null 2>&1 &");
