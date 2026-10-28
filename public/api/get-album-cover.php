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

// Never serve the original or the legacy album root path: older albums may
// still contain full-resolution originals there. Only verified derivatives
// beneath thumbs/{400,800,1200}/ are eligible as a cover.
$albumsRoot = realpath(dirname(__DIR__) . '/albums');
$folderName = (string)$image['albumLocation'];
$filename = basename(str_replace('\\', '/', (string)$image['location']));
if ($albumsRoot === false || $folderName === '' || $filename === '' || $filename === '.' || $filename === '..') {
    http_response_code(404);
    exit();
}
$folder = realpath($albumsRoot . DIRECTORY_SEPARATOR . $folderName);
if ($folder === false || !str_starts_with($folder, $albumsRoot . DIRECTORY_SEPARATOR)) {
    http_response_code(404);
    exit();
}
foreach (['400', '800', '1200'] as $size) {
    $thumbnailRoot = realpath($folder . '/thumbs/' . $size);
    if ($thumbnailRoot === false
        || !str_starts_with($thumbnailRoot, $folder . DIRECTORY_SEPARATOR)) {
        continue;
    }
    $path = realpath($thumbnailRoot . DIRECTORY_SEPARATOR . $filename);
    if ($path === false
        || !str_starts_with($path, $thumbnailRoot . DIRECTORY_SEPARATOR)
        || !is_file($path)) {
        continue;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
        continue;
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit();
}
http_response_code(404);
