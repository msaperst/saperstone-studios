<?php
require_once dirname(__DIR__, 2) . '/src/autoloader.php';

$api = new Api();
try {
    $collection = Album::withId($api->retrieveGetInt('id', 'Collection id'));
    if (!$collection->isCollection()) {
        throw new BadAlbumException('Album is not a collection');
    }
} catch (Exception $exception) {
    Api::setErrorResponseCode($exception);
    echo $exception->getMessage();
    exit();
}
if (!$collection->canUserAccess()) {
    http_response_code(403);
    exit();
}
header('Content-Type: application/json');
header('Cache-Control: private, no-store');
$payload = [
    'id' => $collection->getId(),
    'name' => $collection->getName(),
    'description' => $collection->getDescription(),
    'date' => $collection->getDataArray()['date'],
    'children' => array_map(static fn(Album $child): array => [
        'id' => $child->getId(),
        'name' => $child->getName(),
        'imageCount' => (int)$child->getDataArray()['images'],
    ], $collection->getChildren()),
];
if ($collection->canUserGetData()) {
    $user = User::fromSystem();
    $payload['owner'] = (int)$collection->getOwner();
    $payload['shared'] = $collection->isShared();
    if ($user->isAdmin()) {
        $payload['code'] = $collection->getCode() ?? '';
    }
}
echo json_encode($payload);
