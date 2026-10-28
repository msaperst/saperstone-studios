<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$api->forceLoggedIn();
try {
    $collection = Album::withId($api->retrievePostInt('collection', 'Collection id'));
    $rawOrder = $api->retrievePostString('order', 'Album order');
    $ids = json_decode($rawOrder, true);
    if (!is_array($ids) || !array_is_list($ids) || count(array_filter($ids, 'is_int')) !== count($ids)) {
        throw new BadAlbumException('Album order must be a list of IDs');
    }
    $collection->reorderChildren($ids);
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
