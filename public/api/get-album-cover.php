<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

$api = new Api();
try {
    $album = Album::withId($api->retrieveGetInt('album', 'Album id'));
} catch (Exception $exception) {
    http_response_code(404);
    exit();
}
if (!$album->canUserAccess()) {
    http_response_code(403);
    exit();
}

$candidateId = null;
if (isset($_GET['candidate'])) {
    // A specific photo picker preview is management-only. General clients
    // can request only the public-facing, resolved cover of an album they may
    // view, not browse arbitrary cover candidates through this endpoint.
    if (!$album->canUserGetData()) {
        http_response_code(403);
        exit();
    }
    if ($album->isCollection()) {
        http_response_code(400);
        exit();
    }
    try {
        $candidateId = $api->retrieveGetInt('candidate', 'Candidate image id');
    } catch (Exception $exception) {
        http_response_code(400);
        exit();
    }
}

header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Cross-Origin-Resource-Policy: same-origin');

$image = $album->resolveCoverImage($candidateId);
if ($image === null) {
    http_response_code(404);
    exit();
}

// Always serve a verified derivative through the common protected resolver.
$sourceAlbum = $album->isCollection() ? Album::withId($image['album']) : $album;
$preview = AlbumCoverFile::resolve(
    (string)$image['albumLocation'],
    (string)$image['location'],
    $sourceAlbum->hasThumbnails()
);
if ($preview === null) {
    http_response_code(404);
    exit();
}
header('Content-Type: ' . $preview['mime']);
header('Content-Length: ' . filesize($preview['path']));
readfile($preview['path']);
