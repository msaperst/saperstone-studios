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

foreach ($sql->getRows("SELECT * FROM (SELECT id AS blog FROM `blog_details` WHERE (`title` LIKE ? OR `safe_title` LIKE ?) AND `active` UNION ALL SELECT blog FROM `blog_texts` WHERE `text` LIKE ?) AS x GROUP BY `blog` ORDER BY `blog` DESC LIMIT ?, ?", ["%$search%", "%$search%", "%$search%", $start, $howMany]) as $r) {
    $response [] = $sql->getRow("SELECT * FROM `blog_details` WHERE `id` = ?", [$r['blog']]);
}
$sql->disconnect();
echo json_encode($response);
exit ();
