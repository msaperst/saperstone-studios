<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

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
    $search = $sql->escapeString($_GET ['searchTerm']);
} else {
    exit ();
}

foreach ($sql->getRows("SELECT * FROM (SELECT id AS blog FROM `blog_details` WHERE ( `title` LIKE '%$search%' OR `safe_title` LIKE '%$search%' ) AND `active` UNION ALL SELECT blog FROM `blog_texts` WHERE `text` LIKE '%$search%') AS x GROUP BY `blog` DESC LIMIT $start,$howMany;") as $r) {
    $response [] = $sql->getRow("SELECT * FROM `blog_details` WHERE `id` = '" . $r ['blog'] . "';");
}
$sql->disconnect();
echo json_encode($response);
exit ();
