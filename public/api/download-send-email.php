<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
if (!Api::requireMethod('POST')) {
    exit();
}
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $email = $api->retrievePostString('email', 'Email address');
    $file = $api->retrievePostString('file', 'Image file');

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new BadRequestException('Invalid email address provided.');
    }

    $resolvedFile = Api::resolvePublicPath($file, 'api');
    $downloadDirectory = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp');
    $resolvedDirectory = $resolvedFile === null ? false : realpath(dirname($resolvedFile));
    if ($resolvedFile === null
        || $downloadDirectory === false
        || $resolvedDirectory === false
        || $resolvedDirectory !== $downloadDirectory
        || strtolower(pathinfo($resolvedFile, PATHINFO_EXTENSION)) !== 'zip') {
        throw new BadRequestException('Invalid download file provided.');
    }

    $jobToken = DownloadEmailJob::create($email, $resolvedFile);
    $worker = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'send-download-email.php';
    $command = sprintf(
        'php -f %s %s > /dev/null 2>&1 &',
        escapeshellarg($worker),
        escapeshellarg($jobToken)
    );

    $exitCode = 0;
    system($command, $exitCode);
    if ($exitCode !== 0) {
        DownloadEmailJob::delete($jobToken);
        throw new RuntimeException('Unable to start download email job');
    }
} catch (Exception $e) {
    Api::setErrorResponseCode($e);
    echo json_encode(array('error' => $e->getMessage()));
    exit();
}
