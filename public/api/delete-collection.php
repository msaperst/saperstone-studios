<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$api->forceLoggedIn();
try {
    $collection = Album::withId($api->retrievePostInt('id', 'Collection id'));
    if (!$collection->isCollection()) {
        throw new BadAlbumException('Album is not a collection');
    }
    $collection->delete();
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
