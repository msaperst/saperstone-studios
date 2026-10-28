<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$api->forceLoggedIn();
try {
    $collection = Album::withId($api->retrievePostInt('id', 'Collection id'));
    $collection->updateCollection($_POST);
} catch (BadAlbumException $exception) {
    Api::setErrorResponseCode($exception);
    echo $exception->getMessage();
} catch (AlbumException $exception) {
    http_response_code(403);
    echo $exception->getMessage();
} catch (Exception $exception) {
    Api::setErrorResponseCode($exception);
    echo $exception->getMessage();
}
