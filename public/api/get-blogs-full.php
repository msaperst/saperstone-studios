<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

$start = 0;

if (isset ($_GET ['start'])) {
    $start = (int)$_GET ['start'];
}

$sql = new Sql();
$query = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ($_GET ['tag'])) {
    $query = "SELECT DISTINCT id,date FROM blog_tags AS a1";
    $where = " LEFT JOIN blog_details as details ON a1.blog = details.id WHERE ";
    for ($i = 1; $i <= sizeof($_GET['tag']); $i++) {
        if ($i != 1) {
            $query .= " JOIN blog_tags AS a$i USING (blog) ";
        }
        $where .= "a$i.tag = " . $_GET['tag'][$i - 1] . " AND ";
    }
    $where = substr($where, 0, -4);
    $query .= $where . " ORDER BY details.date, details.id DESC, details.id DESC LIMIT $start,1;";
}

$blogDetails = $sql->getRow($query);
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
