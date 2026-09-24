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

foreach ($sql->getRows("SELECT DISTINCT x.blog FROM (SELECT id AS blog FROM `blog_details` WHERE (`title` LIKE ? OR `safe_title` LIKE ?) AND `active` = 1 UNION ALL SELECT texts.blog FROM `blog_texts` AS texts JOIN `blog_details` AS details ON texts.blog = details.id WHERE texts.`text` LIKE ? AND details.`active` = 1) AS x JOIN `blog_details` AS sort_details ON x.blog = sort_details.id ORDER BY sort_details.`date` DESC, sort_details.`id` DESC LIMIT ?, ?", ["%$search%", "%$search%", "%$search%", $start, $howMany]) as $r) {
    $response [] = $sql->getRow("SELECT * FROM `blog_details` WHERE `id` = ? AND `active` = 1", [$r['blog']]);
}
$sql->disconnect();
echo json_encode($response);
exit ();
