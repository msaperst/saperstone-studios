<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
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

// Strictly validate that the input matches a proper email format structure
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('error' => 'Invalid email address provided.'));
    exit();
}

// Escape both parameters explicitly before spawning the child shell process
$escapedEmail = escapeshellarg($email);
$escapedFile = escapeshellarg($file);

$emailDelay = (int)getenv('SEND_EMAIL_AFTER');
$cmd = sprintf(
    "bash -c 'sleep %d; php -f ../../bin/send-download-email.php \"$1\" \"$2\"' _ %s %s > /dev/null 2>&1 &",
    $emailDelay,
    escapeshellarg($email),
    escapeshellarg($file)
);
system($cmd);