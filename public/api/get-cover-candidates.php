<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

$api = new Api();
try {
    $album = Album::withId($api->retrieveGetInt('album', 'Album id'));
} catch (Exception $exception) {
    Api::setErrorResponseCode($exception);
    echo $exception->getMessage();
    exit();
}
if (!$album->canUserGetData()) {
    http_response_code(403);
    exit();
}

if ($album->isCollection()) {
    http_response_code(400);
    echo 'Choose covers on individual albums';
    exit();
}

$start = filter_var($_GET['start'] ?? 0, FILTER_VALIDATE_INT);
$count = filter_var($_GET['count'] ?? 36, FILTER_VALIDATE_INT);
if ($start === false || $start < 0 || $count === false || $count < 1 || $count > 60) {
    http_response_code(400);
    echo 'Invalid cover-image page';
    exit();
}

// All candidate paths stay on the existing authorized cover endpoint.
// Never send raw public file paths or full-resolution image locations to JS.
$sql = new Sql();
try {
    $query = 'SELECT ai.id, ai.album, ai.location AS imageLocation,
                     a.location AS albumLocation, a.thumbsCreated,
                     a.name AS galleryName FROM album_images ai
              JOIN albums a ON a.id = ai.album
              WHERE ai.album = ?
              ORDER BY ai.sequence, ai.id
              LIMIT ?, ?';
    $params = [$album->getId(), $start, $count + 1];
    $rows = $sql->getRows($query, $params);
} finally {
    $sql->disconnect();
}
$hasMore = count($rows) > $count;
if ($hasMore) {
    array_pop($rows);
}
$candidates = array_map(static fn(array $row): array => [
    'id' => (int)$row['id'],
    'album' => (int)$row['album'],
    'galleryName' => $row['galleryName'],
    'preview' => '/api/get-album-cover.php?album=' . (int)$album->getId()
        . '&candidate=' . (int)$row['id'],
    'previewAvailable' => AlbumCoverFile::resolve(
        (string)$row['albumLocation'],
        (string)$row['imageLocation'],
        (bool)$row['thumbsCreated']
    ) !== null,
], $rows);
header('Content-Type: application/json');
header('Cache-Control: private, no-store');
echo json_encode([
    'candidates' => $candidates,
    'nextStart' => $hasMore ? $start + count($rows) : null,
]);
