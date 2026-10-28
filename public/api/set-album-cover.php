<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

if (!Api::requireMethod('POST')) {
    exit();
}
$api = new Api();
$api->forceLoggedIn();

try {
    $album = Album::withId($api->retrievePostInt('album', 'Album id'));
    $imageId = !isset($_POST['image']) || $_POST['image'] === ''
        ? null : $api->retrievePostInt('image', 'Cover image id');
    $album->setCoverImage($imageId);
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
