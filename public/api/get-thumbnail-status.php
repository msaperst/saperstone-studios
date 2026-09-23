<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new Api();
$api->forceLoggedIn();

$statusPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'status' . DIRECTORY_SEPARATOR . 'thumbnail-status.txt';
if (!is_file($statusPath)) {
    http_response_code(404);
    exit();
}

header('Content-Type: text/plain');
readfile($statusPath);
