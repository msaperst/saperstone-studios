<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$api->forceLoggedIn();
try {
    $album = Album::withId($api->retrievePostInt('album', 'Album id'));
    // A blank parent means detach to a standalone album.
    $parentId = !isset($_POST['parent']) || $_POST['parent'] === ''
        ? null : $api->retrievePostInt('parent', 'Parent collection');
    $album->setParent($parentId);
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
