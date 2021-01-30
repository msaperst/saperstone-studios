<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

$response = array();
if ($systemUser->isAdmin()) {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images, albums.lastAccessed, albums.code FROM albums;";
} elseif ($systemUser->isUploader()) {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images, albums.owner FROM albums LEFT JOIN albums_for_users ON albums_for_users.album = albums.id WHERE albums_for_users.user = {$systemUser->getId()} OR albums.owner = {$systemUser->getId()} GROUP BY albums.id;";
} else {
    $query = "SELECT albums.id, albums.name, albums.description, albums.date, albums.images FROM albums_for_users LEFT JOIN albums ON albums_for_users.album = albums.id WHERE albums_for_users.user = '{$systemUser->getId()}' GROUP BY albums.id;";
}
$sanitizedAlbums = array();
$sql = new Sql();
$albums = $sql->getRows($query);
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