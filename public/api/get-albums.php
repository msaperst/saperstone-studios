<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceLoggedIn();

$response = array();
// Keep the existing role-specific response columns for ordinary albums.
// Collection rows add isCollection only when necessary for the UI. Children
// are loaded through get-collection.php instead of duplicated in this list.
$imageCount = "CASE WHEN a.isCollection = 1
    THEN (SELECT COALESCE(SUM(ch.images), 0) FROM albums ch WHERE ch.parent = a.id)
    ELSE a.images END";
if ($systemUser->isAdmin()) {
    $query = "SELECT a.id, a.name, a.description, a.date, $imageCount AS images,
                     a.thumbsCreated, a.lastAccessed, a.code, a.isCollection
              FROM albums a WHERE a.parent IS NULL";
    $params = [];
} elseif ($systemUser->isUploader()) {
    $query = "SELECT a.id, a.name, a.description, a.date, $imageCount AS images,
                     a.thumbsCreated, a.owner, a.isCollection
              FROM albums a
              LEFT JOIN albums_for_users au ON au.album = a.id
              WHERE (au.user = ? OR a.owner = ?)
                AND (a.parent IS NULL OR NOT EXISTS (
                    SELECT 1 FROM albums p
                    LEFT JOIN albums_for_users pa ON pa.album = p.id AND pa.user = ?
                    WHERE p.id = a.parent AND (p.owner = ? OR pa.user IS NOT NULL)
                ))
              GROUP BY a.id";
    $params = [$systemUser->getId(), $systemUser->getId(),
               $systemUser->getId(), $systemUser->getId()];
} else {
    $query = "SELECT a.id, a.name, a.description, a.date, $imageCount AS images,
                     a.isCollection
              FROM albums_for_users au
              LEFT JOIN albums a ON au.album = a.id
              WHERE au.user = ?
                AND (a.parent IS NULL OR NOT EXISTS (
                    SELECT 1 FROM albums_for_users pa
                    WHERE pa.user = ? AND pa.album = a.parent
                ))
              GROUP BY a.id";
    $params = [$systemUser->getId(), $systemUser->getId()];
}
$sanitizedAlbums = array();
$sql = new Sql();
$albums = $sql->getRows($query, $params);
foreach ($albums as $album) {
    if ($album ['date'] != NULL) {
        $album ['date'] = substr($album ['date'], 0, 10);
    }
    if ($album['id'] != NULL) {
        if ((int)$album['isCollection'] === 1) {
            $album['isCollection'] = true;
        } else {
            unset($album['isCollection']);
        }
        array_push($sanitizedAlbums, $album);
    }
}
echo "{\"data\":" . json_encode($sanitizedAlbums) . "}";
$sql->disconnect();
exit ();
