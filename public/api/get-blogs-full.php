<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$start = 0;

if (isset ($_GET ['start'])) {
    $start = ( int )$_GET ['start'];
}

$query = "SELECT * FROM `blog_details` WHERE `active` ORDER BY `date` DESC LIMIT $start,1;";
if (isset ($_GET ['tag'])) {
    $query = "SELECT DISTINCT blog FROM blog_tags AS a1";
    $where = " WHERE ";
    for ($i = 1; $i <= sizeof($_GET['tag']); $i++) {
        if ($i != 1) {
            $query .= " JOIN blog_tags AS a$i USING (blog) ";
        }
        $where .= "a$i.tag = " . $_GET['tag'][$i - 1] . " AND ";
    }
    $where = substr($where, 0, -4);
    $blogs = $sql->getRows($query . $where);

    if (sizeof($blogs) > 0) {
        $what = "";
        foreach ($blogs as $blog) {
            $what .= "id = " . (int)$blog['blog'] . " OR ";
        }
        $what = substr($what, 0, -3);
    } else {
        $what = 'id = -1';
    }
    $query = "SELECT DISTINCT * FROM `blog_details` WHERE `active` AND ( $what ) ORDER BY `date` DESC LIMIT $start,1;";
}
$blogDetails = $sql->getRow($query);
$sql->disconnect();

try {
    $blog = new Blog($blogDetails ['id']);
} catch (Exception $e) {
    exit();
}

echo json_encode($blog->getDataArray());
exit ();
