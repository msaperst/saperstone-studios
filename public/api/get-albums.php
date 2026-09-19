<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

$response = array();
if ($systemUser->isAdmin()) {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images, albums.lastAccessed, albums.code FROM albums;";
    $params = [];
} elseif ($systemUser->isUploader()) {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images, albums.owner FROM albums LEFT JOIN albums_for_users ON albums_for_users.album = albums.id WHERE albums_for_users.user = ? OR albums.owner = ? GROUP BY albums.id;";
    $params = [$systemUser->getId(), $systemUser->getId()];
} else {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images FROM albums_for_users LEFT JOIN albums ON albums_for_users.album = albums.id WHERE albums_for_users.user = ? GROUP BY albums.id;";
    $params = [$systemUser->getId()];
}
$sanitizedAlbums = array();
$sql = new Sql();
$albums = $sql->getRows($query, $params);
foreach ($albums as $album) {
    if ($album ['date'] != NULL) {
        $album ['date'] = substr($album ['date'], 0, 10);
    }
    if ($album['id'] != NULL) {
        array_push($sanitizedAlbums, $album);
    }
}
echo "{\"data\":" . json_encode($sanitizedAlbums) . "}";
$sql->disconnect();
exit ();
