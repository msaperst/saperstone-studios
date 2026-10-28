<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$user = User::fromSystem();

if (!$user->isAdmin() && !$user->isUploader()) {
    http_response_code(403);
    exit();
}
if (!Api::requireCsrfProtection()) {
    echo Api::CSRF_ERROR;
    exit();
}
try {
    $collection = Album::withParams($_POST);
    echo $collection->createCollection();
} catch (Exception $exception) {
    Api::setErrorResponseCode($exception);
    echo $exception->getMessage();
}
