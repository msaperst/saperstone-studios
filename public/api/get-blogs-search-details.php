<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$response = [];
$start = 0;
$howMany = 999999999999999999;

if (isset ($_GET ['start'])) {
    $start = (int)$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = (int)$_GET ['howMany'];
}
$sql = new Sql ();
if (isset ($_GET ['searchTerm'])) {
    $search = $_GET['searchTerm'];
} else {
    exit ();
}

foreach ($sql->getRows(
    "SELECT details.id AS blog
     FROM blog_details AS details
     WHERE details.active = 1
       AND (
           details.title LIKE ?
           OR details.safe_title LIKE ?
           OR EXISTS (
               SELECT 1
               FROM blog_texts AS texts
               WHERE texts.blog = details.id
                 AND texts.text LIKE ?
           )
       )
     ORDER BY details.date DESC, details.id DESC
     LIMIT ?, ?",
    ["%$search%", "%$search%", "%$search%", $start, $howMany]
) as $r) {
    $response [] = $sql->getRow("SELECT * FROM `blog_details` WHERE `id` = ? AND `active` = 1", [$r['blog']]);
}
$sql->disconnect();
echo json_encode($response);
exit ();
