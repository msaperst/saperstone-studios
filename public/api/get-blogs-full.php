<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$start = 0;

if (isset ($_GET ['start'])) {
    $start = (int)$_GET ['start'];
}

$sql = new Sql();
$query = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC, `id` DESC LIMIT ?, 1";
$params = [$start];
if (isset ($_GET ['tag'])) {
    $query = "SELECT DISTINCT details.id, details.date FROM blog_tags AS a1";
    $where = " JOIN blog_details AS details ON a1.blog = details.id WHERE details.active = 1 AND ";
    for ($i = 1; $i <= sizeof($_GET['tag']); $i++) {
        if ($i != 1) {
            $query .= " JOIN blog_tags AS a$i USING (blog) ";
        }
        $where .= "a$i.tag = ? AND ";
    }
    $where = substr($where, 0, -4);
    $query .= $where . " ORDER BY details.date DESC, details.id DESC LIMIT ?, 1";
    $params = array_merge(array_values($_GET['tag']), [$start]);
}

$blogDetails = $sql->getRow($query, $params);
$sql->disconnect();

try {
    if (!isset ($blogDetails ['id'])) {
        throw new BadBlogException("Blog id is required");
    }
    $blog = Blog::withId($blogDetails ['id']);
} catch (Exception $e) {
    exit();
}

echo json_encode($blog->getDataArray());
exit ();
